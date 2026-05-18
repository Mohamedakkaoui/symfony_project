<?php

namespace App\Service;

use App\DTO\Product\CreateCategoryDTO;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    public function __construct(private EntityManagerInterface $em,private CategoryRepository $categoryRepository)
    {}

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
    public function findCategory($id): ?Category
    {
        return $this->categoryRepository->find($id);
    }
    public function createCategory(CreateCategoryDTO $dto): Category
    {
        $category = new Category();
        $category->setName($dto->name);
        $category->setDescription($dto->description);
        $this->em->persist($category);
        $this->em->flush();
        return $category;
    }

    public function deleteCategory($id): void
    {
        $category = $this->findCategory($id);
        $this->em->remove($category);
        $this->em->flush();
    }

    /**
     * @throws \Exception
     */
    public function updateCategory($id, CreateCategoryDTO $dto): Category
    {
        $category = $this->findCategory($id);
        if (!$category) {
            throw new \Exception('Category not found');
        }
        $category->setName($dto->name);
        $category->setDescription($dto->description);
        $this->em->flush();
        return $category;
    }
}
