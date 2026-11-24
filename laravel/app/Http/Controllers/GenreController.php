<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Genre::all()]);
    }

    public function show(Genre $genre): JsonResponse
    {
        return response()->json(['data' => $genre]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255']);

        return response()->json(['data' => Genre::create($data)], 201);
    }

    public function update(Request $request, Genre $genre): JsonResponse
    {
        $data = $request->validate(['name' => 'sometimes|string|max:255']);
        $genre->update($data);

        return response()->json(['data' => $genre]);
    }

    public function destroy(Genre $genre): JsonResponse
    {
        $genre->delete();

        return response()->json(null, 204);
    }
}