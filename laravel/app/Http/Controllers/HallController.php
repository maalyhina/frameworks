<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HallController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Hall::all()]);
    }

    public function show(Hall $hall): JsonResponse
    {
        return response()->json(['data' => $hall]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
        ]);

        return response()->json(['data' => Hall::create($data)], 201);
    }

    public function update(Request $request, Hall $hall): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
        ]);
        $hall->update($data);

        return response()->json(['data' => $hall]);
    }

    public function destroy(Hall $hall): JsonResponse
    {
        $hall->delete();

        return response()->json(null, 204);
    }
}