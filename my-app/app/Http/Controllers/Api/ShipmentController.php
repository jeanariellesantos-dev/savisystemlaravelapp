<?php

namespace App\Http\Controllers\Api;

use App\Models\Shipment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    /**
     * List shipments (optionally filtered by request_id)
     */
    public function index(Request $request)
    {
        $shipments = Shipment::query()
            ->when($request->request_id, function ($q) use ($request) {
                $q->where('request_id', $request->request_id);
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'request_id', 'batch_number', 'status', 'shipped_date', 'received_date']);

        return response()->json($shipments);
    }

    /**
     * Create a shipment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:requests,id',
            'batch_number' => 'required|string',
            'shipped_by' => 'required|exists:users,id',
            'shipped_date' => 'nullable|date',
            'received_date' => 'nullable|date',
            'status' => 'required|string',
        ]);

        $shipment = Shipment::create($validated);

        return response()->json([
            'message' => 'Shipment created successfully',
            'data' => $shipment
        ], 201);
    }

    /**
     * Update shipment status
     */
    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'received_date' => 'nullable|date',
        ]);

        $shipment->update($validated);

        return response()->json([
            'message' => 'Shipment status updated',
            'data' => $shipment
        ]);
    }

    /**
     * Delete a shipment
     */
    public function destroy(Shipment $shipment)
    {
        $shipment->delete();

        return response()->json([
            'message' => 'Shipment deleted successfully'
        ]);
    }
}
