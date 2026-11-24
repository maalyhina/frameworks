<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ListQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private readonly ListQueryService $listQuery)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->listQuery->paginate(Customer::query(), $request, [
            'id' => ['id', 'eq'],
            'name' => ['name', 'like'],
            'email' => ['email', 'like'],
        ]));
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json(['data' => $customer]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
        ]);

        return response()->json(['data' => Customer::create($data)], 201);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:customers,email,' . $customer->id,
        ]);
        $customer->update($data);

        return response()->json(['data' => $customer]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, 204);
    }
}