<?php

namespace App\Http\Controllers\Api\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Request as RequestModel;

class FulfillmentController extends Controller
{
    public function fulfill(Request $request, $id)
{
    abort_unless(auth()->user()->role === 'INVENTORY', 403);

    $req = RequestModel::findOrFail($id);
    $req->status = 'FULFILLED';
    $req->save();

    Approval::create([
        'request_id' => $req->id,
        'user_id' => auth()->id(),
        'action' => 'APPROVED',
        'remarks' => $request->remarks
    ]);

    return response()->json(['message' => 'Request fulfilled']);
}
}
