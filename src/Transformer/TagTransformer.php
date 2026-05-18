<?php

declare(strict_types=1);

namespace App\Transformer;

use App\Entity\Entity;
use App\Entity\Tag;

class TagTransformer extends Transformer
{
    /**
     * @param Tag $entity
     * @return array{id: mixed, createdAt: mixed, name: mixed, updatedAt: mixed}
     */
    protected function data(Entity $entity): array
    {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'updatedAt' => $entity->getUpdatedAt(),
            'createdAt' => $entity->getCreatedAt(),
        ];
    }

    protected function relations(): array
    {
        return [
            'products' => [
                'transformer' => ProductTransformer::class,
                'method' => 'getProducts',
                'all' => true,
            ],
        ];
    }
}
