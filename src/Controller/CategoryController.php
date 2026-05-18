<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Transformer\CategoryTranformer;
use App\Utils\ValidationErrorFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/category', name: 'app_category_')]
final class CategoryController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoryRepository $categoryRepository): Response
    {

        $categories = $categoryRepository->findAll();
        $categoryTransformer = new CategoryTranformer();
        $categoriesTransformed = $categoryTransformer->transformCollection($categories);
        return $this->render('category/index.html.twig',[
            'categories' => $categoriesTransformed
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create()
    {
        return $this->render('category/create.html.twig');
    }
    #[Route('/edit/{id}', name: 'edit', methods: ['GET'])]
    public function edit(CategoryRepository $categoryRepository, $id)
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->redirectToRoute('app_category_index');
        }
        return $this->render('category/edit.html.twig',[
            'category' => $category
        ]);
    }

    #[Route('/store', name: 'store', methods: ['POST'])]
    public function store(  Request $request,
                            EntityManagerInterface $em,
                            ValidatorInterface $validator,
                            ValidationErrorFormatter $validationErrorFormatter
    )
    {
        $data = $request->request->all();

        $category = new Category();
        $category->setName($data['name']);
        $category->setDescription($data['description']);

        $violations = $validator->validate($category);
        $errors = $validationErrorFormatter->format($violations); //[]

        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_category_create');
        }

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute('app_category_index'); // redirect to index page

    }

    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update(Request $request, CategoryRepository $categoryRepository, $id, EntityManagerInterface $em, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {

        $category = $categoryRepository->find($id);
        if (!$category) {
            return $this->redirectToRoute('app_category_index');
        }
        $data = $request->request->all();

        $category->setName($data['name']);
        $category->setDescription($data['description']);

        $violations = $validator->validate($category);
        $errors = $validationErrorFormatter->format($violations); //[]

        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_category_edit', ['id' => $id]);
        }

        $em->persist($category);
        $em->flush();

        return $this->redirectToRoute('app_category_index'); // redirect to index page
    }
}
