<?php

namespace App\Service;

use App\DTO\Product\TagDTO;
use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Transformer\TagTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class TagService
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly TagRepository $tagRepository)
    {}

    public function getTags(): array
    {
        $tags = $this->tagRepository->findAll();
        $tagsTransformer= new TagTransformer();
        return $tagsTransformer->transformCollection($tags);
    }

    public function createTag(TagDTO  $tagDTO) : Tag
    {
        $tag =new Tag();
        $tag->setName($tagDTO->name);
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        return $tag;
    }

    /**
     * @throws Exception
     */
    public function deleteTag($tagId) : void
    {

        $tag = $this->tagRepository->find($tagId);
        if (!$tag) {
            throw new Exception("Tag not found");
        }
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
        return;
    }

    /**
     * @throws Exception
     */
    public function updateTag($id, TagDTO $tagDTO) : Tag
    {
        $tag = $this->tagRepository->find($id);
        if (!$tag) {
            throw new Exception("Tag not found");
        }
        $tag->setName($tagDTO->name);
        $this->entityManager->flush();
        return $tag;
    }
}
