<?php

namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function requestStatusFigures(Request $request)
    {
        $query = DB::table('requests');

        $this->applyDateFilter($query, $request, 'requests.created_at');

        $stats = $query->selectRaw("
            SUM(CASE 
                WHEN status IN (
                    'PENDING_ACCOUNTING',
                    'PENDING_SUPERVISOR',
                    'PENDING_CLUSTER_HEAD'
                )
                THEN 1 ELSE 0 END) as pending,

            SUM(CASE WHEN status = 'PENDING_INVENTORY' THEN 1 ELSE 0 END) as approved,

            SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) as rejected,

            SUM(CASE WHEN status = 'SHIPPED' THEN 1 ELSE 0 END) as shipped,

            SUM(CASE WHEN status = 'RECEIVED' THEN 1 ELSE 0 END) as received,

            SUM(CASE WHEN status = 'ON_HOLD' THEN 1 ELSE 0 END) as on_hold,

            SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled
        ")->first();

        return response()->json([
            'pending'   => (int) $stats->pending,
            'approved'  => (int) $stats->approved,
            'rejected'  => (int) $stats->rejected,
            'shipped'   => (int) $stats->shipped,
            'received'  => (int) $stats->received,
            'on_hold'   => (int) $stats->on_hold,
            'cancelled' => (int) $stats->cancelled,
        ]);
    }

    public function mostOrderedProducts(Request $request)
    {
        $query = DB::table('request_items')
            ->join('requests', 'requests.id', '=', 'request_items.request_id')
            ->join('products', 'products.id', '=', 'request_items.product_id')
            ->where('requests.status', 'received');

        // apply filter
        $this->applyDateFilter($query, $request, 'requests.created_at');

        $products = $query
            ->select(
                'products.product_name',
                DB::raw('SUM(request_items.quantity) as total_orders')
            )
            ->groupBy('products.id', 'products.product_name')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();

        return response()->json([
            'categories' => $products->pluck('product_name'),
            'series' => $products->pluck('total_orders'),
        ]);
    }

private function applyDateFilter($query, Request $request, string $column = 'requests.created_at')
{
    // CUSTOM RANGE (priority)
    if ($request->start_date && $request->end_date) {
        return $query->whereBetween($column, [
            Carbon::parse($request->start_date)->startOfDay(),
            Carbon::parse($request->end_date)->endOfDay(),
        ]);
    }

    switch ($request->range) {
        case '7d':
            $query->where($column, '>=', now()->subDays(7));
            break;

        case '30d':
            $query->where($column, '>=', now()->subDays(30));
            break;

        case 'year':
            $query->whereYear($column, now()->year);
            break;
    }

    return $query;
}
    public function monthlyRequests(Request $request)
{
    $query = DB::table('requests');

    // apply global date filter
    $this->applyDateFilter($query, $request, 'requests.created_at');

    $data = $query
        ->selectRaw("
            MONTH(created_at) as month,
            COUNT(*) as total
        ")
        ->groupByRaw("MONTH(created_at)")
        ->orderByRaw("MONTH(created_at)")
        ->get();

    // month labels
    $months = collect([
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
        7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
    ]);

    return response()->json([
        'categories' => $data->pluck('month')->map(fn($m) => $months[$m]),
        'series' => $data->pluck('total'),
    ]);
}

public function avgApprovalTime(Request $request)
{
    $query = DB::table('requests')
        ->where('status', 'received')        // ✅ only received requests
        ->whereNotNull('updated_at');        // safety check

    // apply global date filter
    $this->applyDateFilter($query, $request, 'requests.created_at');

    $data = $query
        ->selectRaw("
            MONTH(created_at) as month,
            AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours
        ")
        ->groupByRaw("MONTH(created_at)")
        ->orderByRaw("MONTH(created_at)")
        ->get();

    // month labels
    $months = collect([
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
        7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
    ]);

    return response()->json([
        'categories' => $data->pluck('month')
            ->map(fn ($m) => $months[$m]),

        'series' => $data->pluck('avg_hours')
            ->map(fn ($v) => round($v, 1)),
    ]);
}


public function deliveryLeadTime(Request $request)
{
    $query = DB::table('requests')
        ->join('approvals', function ($join) {
            $join->on('approvals.request_id', '=', 'requests.id')
                 ->where('approvals.action', 'shipped');
        })
        ->where('requests.status', 'received');

    // apply global dashboard filter
    $this->applyDateFilter($query, $request, 'requests.updated_at');

    $data = $query
        ->selectRaw("
            MONTH(requests.updated_at) as month,
            AVG(
                TIMESTAMPDIFF(
                    DAY,
                    approvals.created_at,
                    requests.updated_at
                )
            ) as avg_days
        ")
        ->groupByRaw("MONTH(requests.updated_at)")
        ->orderByRaw("MONTH(requests.updated_at)")
        ->get();

    $months = collect([
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
        7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
    ]);

    return response()->json([
        'categories' => $data->pluck('month')
            ->map(fn ($m) => $months[$m]),
        'series' => $data->pluck('avg_days')
            ->map(fn ($v) => round($v, 1)),
    ]);
}
    
}