<?php

namespace App\Controller;

use App\DTO\Product\TagDTO;
use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Service\TagService;
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
    public function __construct(private TagService $tagService) {}

    #[Route('/', name: 'index')]
    public function index(TagRepository $tagRepository) : Response
    {
        $tags = $this->tagService->getTags();
        return $this->render('tag/index.html.twig',[
            'tags' => $tags
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
    public function store(Request $request, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {
        $data = $request->request->all();
        $dto = new TagDTO($data);
        $violations = $validator->validate($dto);
        $errors = $validationErrorFormatter->format($violations);
        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_tag_create');
        }
        $tag = $this->tagService->createTag($dto);
        return $this->redirectToRoute('app_tag_index');
    }

    /**
     * @throws \Exception
     */
    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update(Request $request, $id, ValidatorInterface $validator, ValidationErrorFormatter $validationErrorFormatter)
    {
        $data = $request->request->all();
        $dto = new TagDTO($data);
        $violations = $validator->validate($dto);
        $errors = $validationErrorFormatter->format($violations);
        if (count($errors) > 0) {
            $this->addFlash('error', $errors);
            return $this->redirectToRoute('app_tag_edit', ['id' => $id]);
        }
        $tag = $this->tagService->updateTag($id, $dto);
        return $this->redirectToRoute('app_tag_index');
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE', 'POST'])]
    public function delete($id)
    {
        try {
            $this->tagService->deleteTag($id);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('app_tag_index');
    }

}
