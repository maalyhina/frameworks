<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MovieController extends Controller
{
    private const MOVIES = [
        ['id' => 1, 'title' => 'Inception', 'description' => 'Sci-fi thriller', 'price' => 150],
        ['id' => 2, 'title' => 'Interstellar', 'description' => 'Space drama', 'price' => 170],
        ['id' => 3, 'title' => 'The Matrix', 'description' => 'Cyberpunk action', 'price' => 140],
    ];

    public function getMovies(): JsonResponse
    {
        return response()->json(['data' => self::MOVIES], Response::HTTP_OK);
    }

    public function getMovieItem(int $id): JsonResponse
    {
        $movie = $this->findMovieById($id);

        if (!$movie) {
            return response()->json(['data' => ['error' => 'Not found movie by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $movie], Response::HTTP_OK);
    }

    public function createMovie(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['title'], $requestData['description'], $requestData['price'])) {
            return response()->json(['data' => ['error' => 'title, description and price are required']], Response::HTTP_BAD_REQUEST);
        }

        $newMovieData = [
            'id'          => max(array_column(self::MOVIES, 'id')) + 1,
            'title'       => $requestData['title'],
            'description' => $requestData['description'],
            'price'       => $requestData['price'],
        ];

        // TODO insert to db

        return response()->json(['data' => $newMovieData], Response::HTTP_CREATED);
    }

    public function updateMovie(int $id, Request $request): JsonResponse
    {
        $movie = $this->findMovieById($id);

        if (!$movie) {
            return response()->json(['data' => ['error' => 'Not found movie by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];

        $updatedMovie = [
            'id'          => $movie['id'],
            'title'       => $requestData['title'] ?? $movie['title'],
            'description' => $requestData['description'] ?? $movie['description'],
            'price'       => $requestData['price'] ?? $movie['price'],
        ];

        // TODO update in db

        return response()->json(['data' => $updatedMovie], Response::HTTP_OK);
    }

    public function deleteMovie(int $id): JsonResponse
    {
        $movie = $this->findMovieById($id);

        if (!$movie) {
            return response()->json(['data' => ['error' => 'Not found movie by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        // TODO delete from db

        return response()->json(null, Response::HTTP_NO_CONTENT);
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