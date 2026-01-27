<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Http\Requests\AddRequest;
use App\Http\Requests\UpdateRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class RequestController extends Controller
{
    //
    public function index(Request $request)
    {
    $query = RequestModel::query();

    // 🔍 Search filter
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('status', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // 🏷 Status filter
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $query->orderBy('created_at', 'asc');
    // 📄 Pagination
    $perPage = $request->get('per_page', 10);

    return response()->json(
        $query->latest()->paginate($perPage)
    );
    }


    //Add new request
    public function store(AddRequest $request)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Log::info($request);

        $req = RequestModel::create($validated);

        return response()->json([
        'message' => 'Request created successfully',
        'data' => $req
        ], 201);
    }


    public function show($id)
    {
        $request = RequestModel::with(['approvals' => function ($q) {
        $q->latest();
         }])->findOrFail($id);

    $latestApproval = $request->approvals->first();

    return response()->json([
        'id' => $request->id,
        'request_id' => $request->request_id,
        'description' => $request->description,
        'status' => $request->status,
        'date' => Carbon::parse($request->created_at)->format('Y-m-d, H:i'),
        'approval_remarks' => $latestApproval->remarks ?? ''
    ]);

        // return response()->json(
        //     RequestModel::findOrFail($id)
        // );
    }

    public function update(UpdateRequest $request, $id)
    {
        $requestData = RequestModel::findOrFail($id);
        $requestData->update($request->validated());

        return response()->json([
            'message' => 'Request updated successfully',
            'data' => $requestData,
        ]);
    }

    public function destroy($id)
    {
        RequestModel::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Request deleted successfully',
        ]);
    }

    public function pending()
    {
        $role = auth()->user()->role;

        $statusMap = [
            'ACCOUNTING' => 'PENDING_ACCOUNTING',
            'SUPERVISOR' => 'PENDING_SUPERVISOR',
            'INVENTORY' => 'PENDING_INVENTORY'
        ];

        abort_unless(isset($statusMap[$role]), 403);

        return RequestModel::where('status', $statusMap[$role])->get();
    }


}
