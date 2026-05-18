<?php

namespace App\DTO\Color;
use Symfony\Component\Validator\Constraints as Assert;

class createColorDTO
{

    #[Assert\NotBlank(message: "Name is required")]
    public string $name;

    #[Assert\NotBlank(message: "Code is required")]
    #[Assert\CssColor(formats: Assert\CssColor::HEX_LONG, message: "Wrong Color")]
    public string $code;

    public function __construct(array $color)
    {
        $this->name = $color['name'];
        $this->code = $color['code'];
    }
}
