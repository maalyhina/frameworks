<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use App\Service\ListQueryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/genres')]
final class GenreController extends AbstractController
{
    public function __construct(
        private readonly GenreRepository $genreRepository,
        private readonly EntityManagerInterface $em,
        private readonly ListQueryService $listQuery,
    ) {
    }

    #[Route('', name: 'genre_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $result = $this->listQuery->paginate(Genre::class, $request, [
            'id' => ['id', 'eq'],
            'name' => ['name', 'like'],
        ]);

        return new JsonResponse([
            'data' => array_map(fn (Genre $g) => $this->toArray($g), $result['items']),
            'meta' => $result['meta'],
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'genre_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $genre = $this->genreRepository->find($id);

        if (!$genre) {
            return $this->notFound($id);
        }

        return new JsonResponse(['data' => $this->toArray($genre)], Response::HTTP_OK);
    }

    #[Route('', name: 'genre_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return new JsonResponse(['data' => ['error' => 'name is required']], Response::HTTP_BAD_REQUEST);
        }

        $genre = (new Genre())->setName($data['name']);
        $this->em->persist($genre);
        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($genre)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'genre_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $genre = $this->genreRepository->find($id);

        if (!$genre) {
            return $this->notFound($id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['name'])) {
            $genre->setName($data['name']);
        }

        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($genre)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'genre_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $genre = $this->genreRepository->find($id);

        if (!$genre) {
            return $this->notFound($id);
        }

        $this->em->remove($genre);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function toArray(Genre $genre): array
    {
        return ['id' => $genre->getId(), 'name' => $genre->getName()];
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => 'Not found genre by id ' . $id]], Response::HTTP_NOT_FOUND);
    }
}