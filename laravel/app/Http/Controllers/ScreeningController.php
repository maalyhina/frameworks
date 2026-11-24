<?php

namespace App\Http\Controllers;

use App\Models\Screening;
use App\Services\ListQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    public function __construct(private readonly ListQueryService $listQuery)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->listQuery->paginate(Screening::query()->with(['movie', 'hall']), $request, [
            'id' => ['id', 'eq'],
            'starts_at' => ['starts_at', 'date'],
            'movie_id' => ['movie_id', 'eq'],
            'hall_id' => ['hall_id', 'eq'],
        ]));
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