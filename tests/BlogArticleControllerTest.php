<?php

namespace App\Tests\Controller;

use App\Controller\BlogArticleController;
use App\Entity\BlogArticle;
use App\Repository\BlogArticleRepository;
use App\Service\WordFrequencyService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BlogArticleControllerTest extends TestCase
{
    private BlogArticleController $controller;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private SluggerInterface $slugger;
    private WordFrequencyService $wordFrequencyService;
    private BlogArticleRepository $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->slugger = $this->createStub(SluggerInterface::class);
        $this->wordFrequencyService = $this->createMock(WordFrequencyService::class);
        $this->repository = $this->createMock(BlogArticleRepository::class);

        // Configure slugger stub with a simple string return
        $this->slugger->method('slug')
            ->willReturnCallback(function($string) {
                return strtolower(str_replace(' ', '-', $string));
            });

        $this->controller = new BlogArticleController(
            $this->entityManager,
            $this->serializer,
            $this->validator,
            $this->slugger,
            $this->wordFrequencyService
        );
    }

    public function testIndex(): void
    {
        $articles = [
            $this->createMock(BlogArticle::class),
            $this->createMock(BlogArticle::class)
        ];
        
        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn($articles);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($articles, 'json')
            ->willReturn('[]');

        $response = $this->controller->index($this->repository);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testCreate(): void
    {
        $requestData = [
            'authorId' => 1,
            'title' => 'Test Title',
            'content' => 'Test Content',
            'status' => 'draft'
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $this->wordFrequencyService->expects($this->once())
            ->method('getMostFrequentWords')
            ->with('Test Content', ['thor'])
            ->willReturn(['test', 'content']);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('{"id":1}');

        $response = $this->controller->create($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreateWithBannedWord(): void
    {
        $requestData = [
            'authorId' => 1,
            'title' => 'Test Title',
            'content' => 'Test Content with thor',
            'status' => 'draft'
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $response = $this->controller->create($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString('not allowed', $response->getContent());
    }

    public function testUpdate(): void
    {
        $article = $this->createMock(BlogArticle::class);
        $requestData = [
            'title' => 'Updated Title',
            'content' => 'Updated Content'
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        
        $this->repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($article);

        $this->wordFrequencyService->expects($this->once())
            ->method('getMostFrequentWords')
            ->with('Updated Content', ['thor'])
            ->willReturn(['updated', 'content']);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('{"id":1}');

        $article->expects($this->once())
            ->method('setTitle')
            ->with('Updated Title');
            
        $article->expects($this->once())
            ->method('setContent')
            ->with('Updated Content');

        $response = $this->controller->update($request, 1, $this->repository);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateWithCoverPicture(): void
    {
        $article = $this->createMock(BlogArticle::class);
        $file = $this->createMock(UploadedFile::class);
        
        $request = new Request([], [], [], [], ['coverPicture' => $file]);
        
        $this->repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($article);

        $file->expects($this->once())
            ->method('guessExtension')
            ->willReturn('jpg');

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('{"id":1}');

        $response = $this->controller->update($request, 1, $this->repository);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testDelete(): void
    {
        $article = $this->createMock(BlogArticle::class);
        
        $this->repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($article);

        $article->expects($this->once())
            ->method('setStatus')
            ->with('deleted');

        $response = $this->controller->delete(1, $this->repository);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testDeleteNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $response = $this->controller->delete(999, $this->repository);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}