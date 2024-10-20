<?php

namespace App\Controller;

use App\Entity\BlogArticle;
use App\Repository\BlogArticleRepository;
use App\Service\WordFrequencyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class BlogArticleController extends AbstractController
{
    private $bannedWords = ['thor']; // Example banned words
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private SluggerInterface $slugger,
        private WordFrequencyService $wordFrequencyService
    ) {
    }

    #[Route('/blog-articles', name: 'blog_article_index', methods: ['GET'])]
    public function index(BlogArticleRepository $blogArticleRepository): JsonResponse
    {
        $blogArticles = $blogArticleRepository->findAll();
        $data = $this->serializer->serialize($blogArticles, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/blog-articles', name: 'blog_article_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $blogArticle = new BlogArticle();
        $blogArticle->setAuthorId($data['authorId']);
        $blogArticle->setTitle($data['title']);
        $blogArticle->setContent($data['content']);
        $blogArticle->setStatus($data['status']);
        $blogArticle->setSlug($this->slugger->slug($data['title'])->lower());

        // Set publication date
        if (isset($data['publicationDate'])) {
            $blogArticle->setPublicationDate(new \DateTime($data['publicationDate']));
        } else {
            // If no publication date is provided, set it to the current date
            $blogArticle->setPublicationDate(new \DateTime());
        }

        // Generate keywords from content
        $keywords = $this->wordFrequencyService->getMostFrequentWords($data['content'], $this->bannedWords);
        $blogArticle->setKeywords($keywords);

        // Validate content for banned words
        $contentLower = strtolower($data['content']);
        foreach ($this->bannedWords as $bannedWord) {
            if (strpos($contentLower, $bannedWord) !== false) {
                return new JsonResponse(['message' => "The word '$bannedWord' is not allowed in the content."], Response::HTTP_BAD_REQUEST);
            }
        }

        $errors = $this->validator->validate($blogArticle);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($blogArticle);
        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializer->serialize($blogArticle, 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/blog-articles/{id}', name: 'blog_article_show', methods: ['GET'])]
    public function show(int $id, BlogArticleRepository $blogArticleRepository): JsonResponse
    {
        $blogArticle = $blogArticleRepository->find($id);

        if (!$blogArticle) {
            return new JsonResponse(['message' => 'Blog article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($blogArticle, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/blog-articles/{id}', name: 'blog_article_update', methods: ['PATCH'])]
    public function update(Request $request, int $id, BlogArticleRepository $blogArticleRepository): JsonResponse
    {
        $blogArticle = $blogArticleRepository->find($id);

        if (!$blogArticle) {
            return new JsonResponse(['message' => 'Blog article not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $blogArticle->setTitle($data['title']);
            $blogArticle->setSlug($this->slugger->slug($data['title'])->lower());
        }
        if (isset($data['content'])) {
            $blogArticle->setContent($data['content']);

            // Validate content for banned words
            $contentLower = strtolower($data['content']);
            foreach ($this->bannedWords as $bannedWord) {
                if (strpos($contentLower, $bannedWord) !== false) {
                    return new JsonResponse(['message' => "The word '$bannedWord' is not allowed in the content."], Response::HTTP_BAD_REQUEST);
                }
            }

            // Update keywords based on new content
            $keywords = $this->wordFrequencyService->getMostFrequentWords($data['content'], $this->bannedWords);
            $blogArticle->setKeywords($keywords);
        }
        if (isset($data['status']))
            $blogArticle->setStatus($data['status']);
        if (isset($data['publicationDate'])) {
            $blogArticle->setPublicationDate(new \DateTime($data['publicationDate']));
        }

        // Handle file upload for cover picture
        if ($request->files->has('coverPicture')) {
            $file = $request->files->get('coverPicture');
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('uploads_directory'), $fileName);
            $blogArticle->setCoverPictureRef($fileName);
        }

        $errors = $this->validator->validate($blogArticle);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializer->serialize($blogArticle, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/blog-articles/{id}', name: 'blog_article_delete', methods: ['DELETE'])]
    public function delete(int $id, BlogArticleRepository $blogArticleRepository): JsonResponse
    {
        $blogArticle = $blogArticleRepository->find($id);

        if (!$blogArticle) {
            return new JsonResponse(['message' => 'Blog article not found'], Response::HTTP_NOT_FOUND);
        }

        $blogArticle->setStatus('deleted');
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}