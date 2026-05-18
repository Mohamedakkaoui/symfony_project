<?php

namespace App\Service;

use App\DTO\Product\TagDTO;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

class TagService
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private TagRepository $tagRepository)
    {}
    public function createTag(TagDTO  $tagDTO)
    {
        $tag =new Tag();
        $tag->setName($tagDTO->name);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        return $tag;
    }
}
