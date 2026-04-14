<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ReportExportLog;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    //
public function inventory(Request $request)
{
    $dealershipId = $request->dealership_id;
    $reportType = $request->report_type ?? 'SUMMARY';

    // date filters
    if ($request->month) {
        $startDate = Carbon::parse($request->month)->startOfMonth();
        $endDate = Carbon::parse($request->month)->endOfMonth();
    } else {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
    }

    // ✅ SUMMARY
    if ($reportType === 'SUMMARY') {
        $data = $this->getInventoryData($dealershipId, $startDate, $endDate);

        return response()->json([
            'type' => 'SUMMARY',
            'data' => $data
        ]);
    }

    // 🔥 MONTHLY MODE
    $months = [];
    $current = $startDate->copy()->startOfMonth();

    while ($current <= $endDate) {
        $months[] = $current->copy();
        $current->addMonth();
    }

    $finalReport = [];

    foreach ($months as $month) {

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // limit to selected range
        if ($start < $startDate) $start = $startDate;
        if ($end > $endDate) $end = $endDate;

        $data = $this->getInventoryData($dealershipId, $start, $end);

        $finalReport[] = [
            'month' => $month->format('F Y'),
            'data' => $data
        ];
    }

    return response()->json([
        'type' => 'MONTHLY',
        'data' => $finalReport
    ]);
}

    private function getInventoryData($dealershipId, $startDate, $endDate)
{
    return DB::table('products as p')

        ->leftJoin(DB::raw("
            (
                SELECT im1.*
                FROM inventory_movements im1
                INNER JOIN (
                    SELECT product_id, MAX(id) as max_id
                    FROM inventory_movements
                    WHERE dealership_id = {$dealershipId}
                    AND created_at <= '{$endDate}'
                    GROUP BY product_id
                ) im2
                ON im1.id = im2.max_id
            ) as last_im
        "), 'p.id', '=', 'last_im.product_id')

        ->leftJoin(DB::raw("
            (
                SELECT product_id, unit_id, SUM(ABS(quantity)) as delivered
                FROM inventory_movements
                WHERE dealership_id = {$dealershipId}
                AND type = 'OUT'
                AND created_at BETWEEN '{$startDate}' AND '{$endDate}'
                GROUP BY product_id, unit_id
            ) as im_out
        "), 'p.id', '=', 'im_out.product_id')

        ->leftJoin(DB::raw("
            (
                SELECT product_id, SUM(quantity) as adjustment
                FROM inventory_movements
                WHERE dealership_id = {$dealershipId}
                AND type = 'ADJUSTMENT'
                AND created_at BETWEEN '{$startDate}' AND '{$endDate}'
                GROUP BY product_id
            ) as im_adjust
        "), 'p.id', '=', 'im_adjust.product_id')

        ->leftJoin(DB::raw("
            (
                SELECT 
                    ri.product_id, 
                    ri.unit_id,
                    SUM(ri.quantity) as ordered
                FROM request_items ri
                INNER JOIN requests r ON r.id = ri.request_id
                INNER JOIN users u ON u.id = r.requestor_id
                WHERE u.dealership_id = {$dealershipId}
                AND r.created_at BETWEEN '{$startDate}' AND '{$endDate}'
                AND r.status <> 'PENDING_ACCOUNTING'
                GROUP BY ri.product_id, ri.unit_id
            ) as req
        "), 'p.id', '=', 'req.product_id')

        ->leftJoin('units as u', 'req.unit_id', '=', 'u.id')
        ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')

        ->select(
            'c.name as category',
            'p.product_name as product',
            'u.name as unit',
            DB::raw('COALESCE(req.ordered, 0) as ordered'),
            DB::raw('COALESCE(im_out.delivered, 0) as delivered'),
            DB::raw('COALESCE(im_adjust.adjustment, 0) as adjustment'),
            DB::raw('COALESCE(last_im.ending_balance, 0) as ending')
        )
        ->whereRaw('COALESCE(req.ordered, 0) > 0')
        ->orderBy('c.name', 'asc')
        ->orderBy('p.product_name', 'asc')
        ->get();
}

public function exportExcel(Request $request)
{
    $response = $this->inventory($request)->getData(true);

    $type = $response['type'];

    // 🔥 convert ALL data to object
    $data = collect($response['data'])->map(function ($item) use ($type) {

        // MONTHLY
        if ($type === 'MONTHLY') {
            return [
                'month' => $item['month'],
                'data' => collect($item['data'])->map(fn($i) => (object) $i)
            ];
        }

        // SUMMARY
        return (object) $item;
    });

    $filename = 'Inventory_Report_' . now()->format('Ymd_His') . '.xlsx';

    ReportExportLog::create([
        'exported_by' => Auth::id(),
        'export_format' => 'EXCEL',
        'text' => $filename,
    ]);

    return Excel::download(
        new InventoryReportExport($data, $type),
        $filename
    );
}

public function exportPdf(Request $request)
{
    $response = $this->inventory($request)->getData(true);

    $type = $response['type'];

    $data = collect($response['data'])->map(function ($item) use ($type) {

        if ($type === 'MONTHLY') {
            return [
                'month' => $item['month'],
                'data' => collect($item['data'])->map(fn($i) => (object) $i)
            ];
        }

        return (object) $item;
    });

    $filename = 'Inventory_Report_' . now()->format('Ymd_His') . '.pdf';

    $pdf = Pdf::loadView('reports.inventory_pdf', [
        'data' => $data,
        'type' => $type,
        'date' => now()->format('F d, Y'),
    ]);

    ReportExportLog::create([
        'exported_by' => Auth::id(),
        'export_format' => 'PDF',
        'text' => $filename,
    ]);

    return $pdf->download($filename);
}

}
