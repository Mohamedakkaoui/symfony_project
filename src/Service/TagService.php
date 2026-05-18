<?php

namespace App\Service;

use App\DTO\Product\TagDTO;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;

class TagService
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private TagRepository $tagRepository)
    {}

    public function createTag(TagDTO  $tagDTO) : Tag
    {
        $tag =new Tag();
        $tag->setName($tagDTO->name);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        return $tag;
    }

    /**
     * @throws \Exception
     */
    public function deleteTag($tagId) : void
    {

        $tag = $this->tagRepository->find($tagId);
        if (!$tag) {
            throw new \Exception("Tag not found");
        }
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
        return;
    }
}
