<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ListQueryService;

#[Route('/movies')]
final class MovieController extends AbstractController
{
    public function __construct(
    private readonly MovieRepository $movieRepository,
    private readonly GenreRepository $genreRepository,
    private readonly EntityManagerInterface $em,
    private readonly ListQueryService $listQuery,
) {
}

    #[Route('', name: 'movie_index', methods: ['GET'])]
public function index(Request $request): JsonResponse
{
    $result = $this->listQuery->paginate(Movie::class, $request, [
        'id' => ['id', 'eq'],
        'title' => ['title', 'like'],
        'description' => ['description', 'like'],
        'duration' => ['duration', 'eq'],
        'price' => ['price', 'eq'],
        'genre_id' => ['genre', 'eq'],
    ]);

    return new JsonResponse([
        'data' => array_map(fn (Movie $m) => $this->toArray($m), $result['items']),
        'meta' => $result['meta'],
    ], Response::HTTP_OK);
}

    #[Route('/{id}', name: 'movie_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $movie = $this->movieRepository->find($id);

        if (!$movie) {
            return $this->notFound($id);
        }

        return new JsonResponse(['data' => $this->toArray($movie)], Response::HTTP_OK);
    }

    #[Route('', name: 'movie_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title']) || !isset($data['duration'], $data['price'], $data['genre_id'])) {
            return new JsonResponse(
                ['data' => ['error' => 'title, duration, price and genre_id are required']],
                Response::HTTP_BAD_REQUEST
            );
        }

        $genre = $this->genreRepository->find((int) $data['genre_id']);

        if (!$genre) {
            return new JsonResponse(['data' => ['error' => 'Genre not found']], Response::HTTP_BAD_REQUEST);
        }

        $movie = (new Movie())
            ->setTitle($data['title'])
            ->setDescription($data['description'] ?? null)
            ->setDuration((int) $data['duration'])
            ->setPrice((string) $data['price'])
            ->setGenre($genre);

        $this->em->persist($movie);
        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($movie)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'movie_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $movie = $this->movieRepository->find($id);

        if (!$movie) {
            return $this->notFound($id);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['title'])) {
            $movie->setTitle($data['title']);
        }
        if (array_key_exists('description', $data)) {
            $movie->setDescription($data['description']);
        }
        if (isset($data['duration'])) {
            $movie->setDuration((int) $data['duration']);
        }
        if (isset($data['price'])) {
            $movie->setPrice((string) $data['price']);
        }
        if (isset($data['genre_id'])) {
            $genre = $this->genreRepository->find((int) $data['genre_id']);

            if (!$genre) {
                return new JsonResponse(['data' => ['error' => 'Genre not found']], Response::HTTP_BAD_REQUEST);
            }

            $movie->setGenre($genre);
        }

        $this->em->flush();

        return new JsonResponse(['data' => $this->toArray($movie)], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'movie_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $movie = $this->movieRepository->find($id);

        if (!$movie) {
            return $this->notFound($id);
        }

        $this->em->remove($movie);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function toArray(Movie $movie): array
    {
        return [
            'id' => $movie->getId(),
            'title' => $movie->getTitle(),
            'description' => $movie->getDescription(),
            'duration' => $movie->getDuration(),
            'price' => $movie->getPrice(),
            'genre' => [
                'id' => $movie->getGenre()?->getId(),
                'name' => $movie->getGenre()?->getName(),
            ],
        ];
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse(['data' => ['error' => 'Not found movie by id ' . $id]], Response::HTTP_NOT_FOUND);
    }
}