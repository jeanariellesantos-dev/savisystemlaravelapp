<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipment;
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
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'received_date' => 'nullable|date',
        ]);

        $requestData = Shipment::findOrFail($id);
        $requestData->update($request->validated());

        return response()->json([
            'message' => 'Shipment status updated',
            'data' => $requestData
        ]);
    }

    /**
     * Delete a shipment
     */
    public function destroy($id)
    {
        Shipment::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Shipment deleted successfully'
        ]);
    }
}
