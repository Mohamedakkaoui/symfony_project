<?php

namespace App\Controller;

use App\DTO\Color\createColorDTO;
use App\DTO\Color\updateColorDTO;
use App\Repository\ColorRepository;
use App\Service\ColorService;
use App\Utils\ValidationErrorFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/color', name: 'app_color_')]
class ColorController extends AbstractController
{

    public function __construct(private readonly ColorService $colorService){}

    #[Route('/', name: 'index')]
    public function index()
    {
        $colorsTransformed = $this->colorService->getColors();
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
        $dto = new createColorDTO($data);
        $violations = $validator->validate($dto);
        $errors = $validationErrorFormatter->format($violations);
        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_color_create');
        }
        $this->colorService->createColor($dto);
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
    public function update(Request $request, $id, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {
        $data = $request->request->all();
        try {
            $dto = new updateColorDTO($data);
            $violations = $validator->validate($dto);
            $errors = $validationErrorFormatter->format($violations);
            if (count($errors) > 0) {
                $this->addFlash('error', $errors);
                return $this->redirectToRoute('app_color_update', ['id' => $id]);
            }
           $this->colorService->updateColor($dto, $id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_color_index');
        }
    }


    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete($id)
    {
        try {
           $this->colorService->deleteColor($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_color_index');
        }
    }

}
