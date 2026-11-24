<?php

namespace App\Http\Controllers;

use App\Models\TicketOrder;
use App\Services\ListQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketOrderController extends Controller
{
    public function __construct(private readonly ListQueryService $listQuery)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->listQuery->paginate(TicketOrder::query()->with(['customer', 'screening']), $request, [
            'id' => ['id', 'eq'],
            'quantity' => ['quantity', 'eq'],
            'created_at' => ['created_at', 'date'],
            'customer_id' => ['customer_id', 'eq'],
            'screening_id' => ['screening_id', 'eq'],
        ]));
    }

    public function show(TicketOrder $ticketOrder): JsonResponse
    {
        return response()->json(['data' => $ticketOrder->load(['customer', 'screening'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
            'customer_id' => 'required|exists:customers,id',
            'screening_id' => 'required|exists:screenings,id',
        ]);
        $order = TicketOrder::create($data);

        return response()->json(['data' => $order->load(['customer', 'screening'])], 201);
    }

    public function update(Request $request, TicketOrder $ticketOrder): JsonResponse
    {
        $data = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'customer_id' => 'sometimes|exists:customers,id',
            'screening_id' => 'sometimes|exists:screenings,id',
        ]);
        $ticketOrder->update($data);

        return response()->json(['data' => $ticketOrder->load(['customer', 'screening'])]);
    }

    public function destroy(TicketOrder $ticketOrder): JsonResponse
    {
        $ticketOrder->delete();

        return response()->json(null, 204);
    }
}