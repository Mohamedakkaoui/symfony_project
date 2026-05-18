<?php

namespace App\Entity;

use App\Trait\DatetimeTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
class Entity
{
    use DatetimeTrait;
}