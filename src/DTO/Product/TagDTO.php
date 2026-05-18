<?php

namespace App\DTO\Product;
use Symfony\Component\Validator\Constraints as Assert;

class TagDTO
{
    #[Assert\NotBlank(message : "Name is required")]
    public string $name;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
    }
}
