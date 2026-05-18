<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'make:transformer',
    description: 'Generate a Transformer class for a Doctrine entity (data() + relations())',
)]
final class MakeTransformerCommand extends Command
{
    private const ENTITY_NAMESPACE = 'App\\Entity\\';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'entity',
            null,
            InputOption::VALUE_REQUIRED,
            'Entity short name or FQCN (e.g. Product or App\\Entity\\Product)',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entityOption = $input->getOption('entity');
        if (! \is_string($entityOption) || $entityOption === '') {
            $io->error('You must pass --entity=YourEntity (e.g. --entity=Tag).');

            return Command::FAILURE;
        }

        $entityClass = $this->resolveEntityClass($entityOption);
        if (! class_exists($entityClass)) {
            $io->error(sprintf('Class "%s" does not exist.', $entityClass));

            return Command::FAILURE;
        }

        if (! is_subclass_of($entityClass, Entity::class)) {
            $io->error(sprintf('"%s" must extend %s.', $entityClass, Entity::class));

            return Command::FAILURE;
        }

        try {
            $metadata = $this->entityManager->getClassMetadata($entityClass);
        } catch (\Throwable $e) {
            $io->error(sprintf('Not a mapped Doctrine entity: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $shortName = $this->classShortName($entityClass);
        $targetPath = \dirname(__DIR__, 2) . '/src/Transformer/' . $shortName . 'Transformer.php';

        $filesystem = new Filesystem();
        if ($filesystem->exists($targetPath)) {
            $io->error(sprintf('Transformer already exists: %s', $targetPath));

            return Command::FAILURE;
        }

        $content = $this->buildTransformerSource($entityClass, $shortName, $metadata);
        $filesystem->dumpFile($targetPath, $content);

        $io->success(sprintf('Created %s', $targetPath));

        return Command::SUCCESS;
    }

    private function resolveEntityClass(string $entity): string
    {
        if (str_contains($entity, '\\')) {
            return ltrim($entity, '\\');
        }

        return self::ENTITY_NAMESPACE . $entity;
    }

    private function classShortName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);

        return $parts[array_key_last($parts)];
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private function buildTransformerSource(string $entityClass, string $shortName, ClassMetadata $metadata): string
    {
        $dataKeys = [];
        $dataLinesByKey = [];
        foreach ($metadata->fieldMappings as $fieldMapping) {
            $fieldName = $fieldMapping->fieldName;
            $getter = $this->getterForFieldName($fieldName);
            if (! method_exists($entityClass, $getter)) {
                continue;
            }
            $arrayKey = $this->arrayKeyForField($fieldName);
            $dataLinesByKey[$arrayKey] = sprintf("            '%s' => \$entity->%s(),", $arrayKey, $getter);
        }

        $this->sortDataKeysFirstIdThenAlpha($dataLinesByKey);
        $dataLines = array_values($dataLinesByKey);
        $sortedDataKeys = array_keys($dataLinesByKey);

        $relationImports = [];
        $relationBlocksByField = [];

        foreach ($metadata->associationMappings as $association) {
            $fieldName = $association->fieldName;
            $getter = $this->getterForFieldName($fieldName);
            if (! method_exists($entityClass, $getter)) {
                continue;
            }

            $targetEntity = $association->targetEntity;
            $targetShort = $this->classShortName($targetEntity);
            $transformerClass = $targetShort . 'Transformer';
            $transformerFqcn = 'App\\Transformer\\' . $transformerClass;

            $relationImports[$transformerFqcn] = true;
            $all = ($association->type() & ClassMetadata::TO_MANY) !== 0;
            $allLiteral = $all ? 'true' : 'false';

            $relationBlocksByField[$fieldName] = sprintf(
                <<<'EOT'
            '%s' => [
                'transformer' => %s::class,
                'method' => '%s',
                'all' => %s,
            ],
EOT
                ,
                $fieldName,
                $transformerClass,
                $getter,
                $allLiteral,
            );
        }

        ksort($relationBlocksByField);
        $relationBlocks = array_values($relationBlocksByField);

        $uses = [
            'use App\\Entity\\Entity;',
            'use App\\Entity\\' . $shortName . ';',
        ];
        foreach (array_keys($relationImports) as $fqcn) {
            if (str_starts_with($fqcn, 'App\\Transformer\\')) {
                continue;
            }
            $uses[] = 'use ' . $fqcn . ';';
        }
        sort($uses);

        $dataMethodBody = $dataLines === []
            ? <<<'PHP'
        return [];
PHP
            : "        return [\n" . implode("\n", $dataLines) . "\n        ];";

        $relationsMethodBody = $relationBlocks === []
            ? <<<'PHP'
        return [];
PHP
            : "        return [\n" . implode("\n", $relationBlocks) . "\n        ];";

        $phpdocReturn = $sortedDataKeys === []
            ? 'array'
            : 'array{' . implode(', ', array_map(static fn (string $k): string => $k . ': mixed', $sortedDataKeys)) . '}';

        $usesBlock = implode("\n", $uses);

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\\Transformer;

{$usesBlock}

class {$shortName}Transformer extends Transformer
{
    /**
     * @param {$shortName} \$entity
     * @return {$phpdocReturn}
     */
    protected function data(Entity \$entity): array
    {
{$dataMethodBody}
    }

    protected function relations(): array
    {
{$relationsMethodBody}
    }
}

PHP;
    }

    /**
     * @param array<string, string> $dataLinesByKey
     */
    private function sortDataKeysFirstIdThenAlpha(array &$dataLinesByKey): void
    {
        uksort($dataLinesByKey, static function (string $a, string $b): int {
            if ($a === 'id') {
                return $b === 'id' ? 0 : -1;
            }
            if ($b === 'id') {
                return 1;
            }

            return strcmp($a, $b);
        });
    }

    private function getterForFieldName(string $fieldName): string
    {
        $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));

        return 'get' . $camel;
    }

    private function arrayKeyForField(string $fieldName): string
    {
        $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName)));

        return lcfirst($camel);
    }
}
