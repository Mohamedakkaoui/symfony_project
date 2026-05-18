<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationErrorFormatter
{
    /**
     * @return array<string, list<string>>
     */
    public static function format(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath() ?: 'global';
            $errors[$path][] = (string) $violation->getMessage();
        }
        return $errors;
    }
}
