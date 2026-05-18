<?php

namespace App\Transformer;

use App\Entity\Category;
use App\Entity\Entity;


class CategoryTranformer extends Transformer
{
    /**
     * Summary of data
     * @param Category $entity
     * @return array{description: mixed, id: mixed, name: mixed}
     */
    protected function data(Entity $entity): array
    {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'description' => $entity->getDescription(),
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