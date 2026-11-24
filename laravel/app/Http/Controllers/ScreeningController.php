<?php

namespace App\Http\Controllers;

use App\Models\Screening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Screening::with(['movie', 'hall'])->get()]);
    }

    public function show(Screening $screening): JsonResponse
    {
        return response()->json(['data' => $screening->load(['movie', 'hall'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'starts_at' => 'required|date',
            'movie_id' => 'required|exists:movies,id',
            'hall_id' => 'required|exists:halls,id',
        ]);
        $screening = Screening::create($data);

        return response()->json(['data' => $screening->load(['movie', 'hall'])], 201);
    }

    public function update(Request $request, Screening $screening): JsonResponse
    {
        $data = $request->validate([
            'starts_at' => 'sometimes|date',
            'movie_id' => 'sometimes|exists:movies,id',
            'hall_id' => 'sometimes|exists:halls,id',
        ]);
        $screening->update($data);

        return response()->json(['data' => $screening->load(['movie', 'hall'])]);
    }

    public function destroy(Screening $screening): JsonResponse
    {
        $screening->delete();

        return response()->json(null, 204);
    }
}
