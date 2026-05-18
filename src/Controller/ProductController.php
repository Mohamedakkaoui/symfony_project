<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ColorRepository;
use App\Repository\ProductRepository;
use App\Repository\TagRepository;
use App\Transformer\ProductTransformer;
use App\Utils\ValidationErrorFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/product', name: 'app_product_')]
final class ProductController extends AbstractController
{


    #[Route('/', name: 'index')]
    public function index(
        ProductRepository $productRepository,
    ): Response {

        $products = $productRepository->findWithRelations(['category']);
        $productTransformer = new ProductTransformer();
        $productsTransformed = $productTransformer->transformCollection($products, [
            'category','tags','colors'
        ]);

        return $this->render('product/index.html.twig',[
            'products' => $productsTransformed
        ]);
    }


    #[Route('/create', name: 'create', methods: ['GET'])]
    public function create(CategoryRepository $categoryRepository, TagRepository $tagRepository, ColorRepository $colorRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $colors = $colorRepository->findAll();
        return $this->render('product/create.html.twig',[
            'categories' => $categories,
            'tags' => $tags,
            'colors' => $colors
        ]);
    }


    #[Route('/store', name: 'store', methods: ['POST'])]
    public function store(Request $request,
                          ValidatorInterface $validator,
                          ValidationErrorFormatter $validationErrorFormatter,
                          EntityManagerInterface $em,
                          CategoryRepository $categoryRepository
    ,TagRepository $tagRepository
    ,ColorRepository $colorRepository)

    {
        $data = $request->request->all();

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice((float)$data['price']);
        $product->setQuantity((int)$data['quantity']); // "" => 0

        $product->setCategory($categoryRepository->find($data['category']));
        $tag = $tagRepository->find($data['tag']);
        $color = $colorRepository->find($data['color']);
        $product->addColor($color);
        $product->addTag($tag);
        $violations = $validator->validate($product);
        $errors = $validationErrorFormatter->format($violations); //[]

        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_product_create');
        }

        $em->persist($product);
        $em->flush();

        return $this->redirectToRoute('app_product_index'); // redirect to index page

    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET'])]
    public function edit(TagRepository $tagRepository, ColorRepository $colorRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository, $id): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            return $this->redirectToRoute('app_product_index');
        }
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $colors = $colorRepository->findAll();
        return $this->render('product/edit.html.twig',[
            'product' => $product,
            'categories' => $categories,
            'tags' => $tags,
            'colors' => $colors
        ]);
    }
    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update( Request $request,
                            ProductRepository $productRepository,
                            $id,
                            EntityManagerInterface $em,
                            CategoryRepository $categoryRepository,
                            ValidatorInterface $validator,
                            ValidationErrorFormatter $validationErrorFormatter,TagRepository $tagRepository, ColorRepository $colorRepository)
    {
        $product = $productRepository->find($id);
        if (!$product) {
            return $this->redirectToRoute('app_product_index');
        }
        $data = $request->request->all();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice((float)$data['price']);
        $product->setQuantity((int)$data['quantity']);
        $product->setCategory($categoryRepository->find($data['category']));
        if (isset($data['tag'])) {
            $product->getTags()->clear();
            $tag = $tagRepository->find($data['tag']);
            $product->addTag($tag);
        }
        if (isset($data['color'])) {
            $product->getColors()->clear();
            $color = $colorRepository->find($data['color']);
            $product->addColor($color);
        }
        $violations = $validator->validate($product);
        $errors = $validationErrorFormatter->format($violations); //[]

        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_product_edit', ['id' => $id]);
        }

        $em->persist($product);
        $em->flush();
        return $this->redirectToRoute('app_product_index');
    }
}

