<?php

namespace App\Transformer;

use App\Entity\Entity;
use App\Entity\Product;

class ProductTransformer extends Transformer
{
    /**
     * Summary of data
     * @param Product $entity
     * @return array{description: mixed, id: mixed, name: mixed, price: mixed, quantity: mixed}
     */
    protected function data(Entity $entity): array
    {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'description' => $entity->getDescription(),
            'price' => $entity->getPrice(),
            'quantity' => $entity->getQuantity(),
        ];
    }
    protected function relations(): array
    {
        return [
            'category' => [
                'transformer' => CategoryTranformer::class,
                'method' => 'getCategory',
                'all' => false,
            ],
            'tags' => [
                'transformer' => TagTransformer::class,
                'method' => 'getTags',
                'all' => true,
            ],
            'colors' => [
                'transformer' => ColorTransformer::class,
                'method' => 'getColors',
                'all' => true,
            ],
        ];
    }
}