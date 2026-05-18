<?php

namespace App\DTO\Product;
use Symfony\Component\Validator\Constraints as Assert;
class CreateCategoryDTO
{
    #[Assert\NotBlanc(message : "Name is required")]
    public string $name;

    #[Assert\NotBlanc(message : "Description is required")]
    public string $description;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
    }
}
