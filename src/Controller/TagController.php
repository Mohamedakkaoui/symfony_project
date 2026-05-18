<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Transformer\TagTransformer;
use App\Utils\ValidationErrorFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/tag', name: 'app_tag_')]
class TagController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(TagRepository $tagRepository) : Response
    {
        $tags = $tagRepository->findAll();
        $tagsTransformer = new TagTransformer();
        $tagsTransformed = $tagsTransformer->transformCollection($tags);
        return $this->render('tag/index.html.twig',[
            'tags' => $tagsTransformed
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create()
    {
        return $this->render('tag/create.html.twig');
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET'])]
    public function edit(TagRepository $tagRepository, $id)
    {
        $tag = $tagRepository->find($id);
        if (!$tag) {
            return $this->redirectToRoute('app_tag_index');
        }
        return $this->render('tag/edit.html.twig',[
            'tag' => $tag
        ]);
    }

    #[Route('/store', name: 'store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {
        $data = $request->request->all();
        $tag = new Tag();
        $tag->setName($data['name']);
        $violations = $validator->validate($tag);
        $errors = $validationErrorFormatter->format($violations);
        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_tag_create');
        }
        $em->persist($tag);
        $em->flush();
        return $this->redirectToRoute('app_tag_index');
    }

    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update(Request $request, $id,TagRepository $tagRepository, EntityManagerInterface $em, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {
        $tag = $tagRepository->find($id);
        if (!$tag) {
            return $this->redirectToRoute('app_tag_index');
        }
        $data = $request->request->all();
        $tag->setName($data['name']);
        $violations = $validator->validate($tag);
        $errors = $validationErrorFormatter->format($violations);
        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
        }
        $em->persist($tag);
        $em->flush();
        return $this->redirectToRoute('app_tag_index');
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE', 'POST'])]
    public function delete($id, TagRepository $tagRepository, EntityManagerInterface $em)
    {
        $tag = $tagRepository->find($id);
        if (!$tag) {
            return $this->redirectToRoute('app_tag_index');
        }
        $em->remove($tag);
        $em->flush();
        return $this->redirectToRoute('app_tag_index');
    }

}
