<?php

namespace App\Controller;

use App\Entity\Color;
use App\Repository\ColorRepository;
use App\Transformer\ColorTransformer;
use App\Utils\ValidationErrorFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/color', name: 'app_color_')]
class ColorController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(ColorRepository $colorRepository)
    {
        $colors = $colorRepository->findAll();
        $colorsTransformer = new ColorTransformer();
        $colorsTransformed = $colorsTransformer->transformCollection($colors);
        return $this->render('color/index.html.twig', [
            'colors' => $colorsTransformed
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create()
    {
        return $this->render('color/create.html.twig');
    }

    #[Route('/store', name: 'store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {
        $data = $request->request->all();
        $color = new Color();
        $color->setName($data['name']);
        $color->setCode($data['code']);
        $violations = $validator->validate($color);
        $errors = $validationErrorFormatter->format($violations);
        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
        }
        $em->persist($color);
        $em->flush();
        return $this->redirectToRoute('app_color_index');
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET'])]
    public function edit(ColorRepository $colorRepository, $id)
    {
            $color = $colorRepository->find($id);
            if (!$color) {
                return $this->redirectToRoute('app_color_index');
            }
            return $this->render('color/edit.html.twig', [
                'color' => $color
            ]);
    }

    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update(Request $request,EntityManagerInterface $em, ColorRepository $colorRepository, $id, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {
        $color = $colorRepository->find($id);
        if (!$color) {
            return $this->redirectToRoute('app_color_index');
        }
        $data = $request->request->all();
        $color->setName($data['name']);
        $color->setCode($data['code']);
        $violations = $validator->validate($color);
        $errors = $validationErrorFormatter->format($violations);
        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
        }
        $em->persist($color);
        $em->flush();
        return $this->redirectToRoute('app_color_index');
    }


    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(ColorRepository $colorRepository, $id, EntityManagerInterface $em)
    {
        $color = $colorRepository->find($id);
        if (!$color) {
            return $this->redirectToRoute('app_color_index');
        }
        $em->remove($color);
        $em->flush();
        return $this->redirectToRoute('app_color_index');
    }

}
