<?php

declare(strict_types=1);

namespace App\Transformer;

use App\Entity\Color;
use App\Entity\Entity;

class ColorTransformer extends Transformer
{
    /**
     * @param Color $entity
     * @return array{id: mixed, code: mixed, createdAt: mixed, name: mixed, updatedAt: mixed}
     */
    protected function data(Entity $entity): array
    {
        return [
            'id' => $entity->getId(),
            'code' => $entity->getCode(),
            'createdAt' => $entity->getCreatedAt(),
            'name' => $entity->getName(),
            'updatedAt' => $entity->getUpdatedAt(),
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
