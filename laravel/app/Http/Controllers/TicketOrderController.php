<?php

namespace App\Http\Controllers;

use App\Models\TicketOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketOrderController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => TicketOrder::with(['customer', 'screening'])->get()]);
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