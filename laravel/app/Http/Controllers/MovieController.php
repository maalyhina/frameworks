<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Services\ListQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function __construct(private readonly ListQueryService $listQuery)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->listQuery->paginate(Movie::query()->with('genre'), $request, [
            'id' => ['id', 'eq'],
            'title' => ['title', 'like'],
            'description' => ['description', 'like'],
            'duration' => ['duration', 'eq'],
            'price' => ['price', 'eq'],
            'genre_id' => ['genre_id', 'eq'],
        ]));
    }

    public function show(Movie $movie): JsonResponse
    {
        return response()->json(['data' => $movie->load('genre')]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'genre_id' => 'required|exists:genres,id',
        ]);
        $movie = Movie::create($data);

        return response()->json(['data' => $movie->load('genre')], 201);
    }

    public function update(Request $request, Movie $movie): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'genre_id' => 'sometimes|exists:genres,id',
        ]);
        $movie->update($data);

        return response()->json(['data' => $movie->load('genre')]);
    }

    public function destroy(Movie $movie): JsonResponse
    {
        $movie->delete();

        return response()->json(null, 204);
    }
}