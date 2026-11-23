<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MovieController extends AbstractController
{
    private const MOVIES = [
        ['id' => 1, 'title' => 'Inception', 'description' => 'Sci-fi thriller', 'price' => 150],
        ['id' => 2, 'title' => 'Interstellar', 'description' => 'Space drama', 'price' => 170],
        ['id' => 3, 'title' => 'The Matrix', 'description' => 'Cyberpunk action', 'price' => 140],
    ];

    #[Route('/movies', name: 'get_movies', methods: [Request::METHOD_GET])]
    public function getMovies(): JsonResponse
    {
        return new JsonResponse(['data' => self::MOVIES], Response::HTTP_OK);
    }

    #[Route('/movies/{id}', name: 'get_movie_item', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getMovieItem(int $id): JsonResponse
    {
        $movie = $this->findMovieById($id);

        if (!$movie) {
            return new JsonResponse(['data' => ['error' => 'Not found movie by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $movie], Response::HTTP_OK);
    }

    #[Route('/movies', name: 'post_movies', methods: [Request::METHOD_POST])]
    public function createMovie(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['title'], $requestData['description'], $requestData['price'])) {
            return new JsonResponse(['data' => ['error' => 'title, description and price are required']], Response::HTTP_BAD_REQUEST);
        }

        $newMovieData = [
            'id'          => max(array_column(self::MOVIES, 'id')) + 1,
            'title'       => $requestData['title'],
            'description' => $requestData['description'],
            'price'       => $requestData['price'],
        ];

        // TODO insert to db

        return new JsonResponse(['data' => $newMovieData], Response::HTTP_CREATED);
    }

    #[Route('/movies/{id}', name: 'put_movie', requirements: ['id' => '\d+'], methods: [Request::METHOD_PUT])]
    public function updateMovie(int $id, Request $request): JsonResponse
    {
        $movie = $this->findMovieById($id);

        if (!$movie) {
            return new JsonResponse(['data' => ['error' => 'Not found movie by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];

        $updatedMovie = [
            'id'          => $movie['id'],
            'title'       => $requestData['title'] ?? $movie['title'],
            'description' => $requestData['description'] ?? $movie['description'],
            'price'       => $requestData['price'] ?? $movie['price'],
        ];

        // TODO update in db

        return new JsonResponse(['data' => $updatedMovie], Response::HTTP_OK);
    }

    #[Route('/movies/{id}', name: 'delete_movie', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteMovie(int $id): JsonResponse
    {
        $movie = $this->findMovieById($id);

        if (!$movie) {
            return new JsonResponse(['data' => ['error' => 'Not found movie by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        // TODO delete from db

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function findMovieById(int $id): ?array
    {
        foreach (self::MOVIES as $movie) {
            if ($movie['id'] === $id) {
                return $movie;
            }
        }

        return null;
    }
}