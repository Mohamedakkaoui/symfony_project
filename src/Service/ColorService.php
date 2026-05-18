<?php

namespace App\Service;

use App\DTO\Color\createColorDTO;
use App\DTO\Color\updateColorDTO;
use App\Entity\Color;
use App\Repository\ColorRepository;
use App\Transformer\ColorTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ColorService
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly ColorRepository $colorRepository) {}

    public function getColors()
    {
        $colors = $this->colorRepository->findAll();
        $colorsTransformer = new ColorTransformer();
        return $colorsTransformer->transformCollection($colors);
    }

    public function createColor(createColorDTO $colorDTO)
    {
        $color = new Color();
        $color->setName($colorDTO->name);
        $color->setCode($colorDTO->code);
        $this->entityManager->persist($color);
        $this->entityManager->flush();
        return $color;
    }

    public function deleteColor($id)
    {
        $color = $this->colorRepository->find($id);
        if (!$color) {
            throw new NotFoundHttpException('Color not found');
        }
        $this->entityManager->remove($color);
        $this->entityManager->flush();
        return true;
    }

    public function updateColor(updateColorDTO $colorDTO, $id)
    {
        $color = $this->colorRepository->find($id);
        if (!$color) {
            throw new NotFoundHttpException('Color not found');
        }
        $color->setName($colorDTO->name);
        $color->setCode($colorDTO->code);
        $this->entityManager->persist($color);
        $this->entityManager->flush();
        return $color;
    }
}
