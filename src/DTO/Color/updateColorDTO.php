<?php

namespace App\DTO\Color;
use Symfony\Component\Validator\Constraints as Assert;

class updateColorDTO
{
    #[Assert\Length(min : 2, max : 10)]
    public string $name;

    #[Assert\CssColor(formats: Assert\CssColor::HEX_LONG, message: "Wrong Color")]
    public string $code;

    public function __construct(array $color)
    {
        $this->name = $color['name'];
        $this->code = $color['code'];
    }
}
