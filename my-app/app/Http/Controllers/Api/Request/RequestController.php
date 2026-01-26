<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Http\Requests\AddRequest;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
    //
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
}
