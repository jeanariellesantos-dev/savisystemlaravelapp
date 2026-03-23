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

        // date filters
        if ($request->month) {
            $startDate = Carbon::parse($request->month)->startOfMonth();
            $endDate = Carbon::parse($request->month)->endOfMonth();
        } else {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
        }

        $report = DB::table('products as p')

            // 🔹 JOIN latest inventory movement (ENDING BALANCE)
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

            ->leftJoin('units as u', 'last_im.unit_id', '=', 'u.id')

            // 🔹 ACTUAL DELIVER (inventory movements OUT)
            ->leftJoin(DB::raw("
                (
                    SELECT product_id, SUM(ABS(quantity)) as delivered
                    FROM inventory_movements
                    WHERE dealership_id = {$dealershipId}
                    AND type = 'OUT'
                    AND created_at BETWEEN '{$startDate}' AND '{$endDate}'
                    GROUP BY product_id
                ) as im_out
            "), 'p.id', '=', 'im_out.product_id')

            // 🔹 ORDERED (request_items)
            ->leftJoin(DB::raw("
                (
                    SELECT 
                        ri.product_id, 
                        SUM(ri.quantity) as ordered
                    FROM request_items ri
                    INNER JOIN requests r ON r.id = ri.request_id
                    INNER JOIN users u ON u.id = r.requestor_id
                    WHERE u.dealership_id = {$dealershipId}
                    AND r.created_at BETWEEN '{$startDate}' AND '{$endDate}'
                    GROUP BY ri.product_id
                ) as req
            "), 'p.id', '=', 'req.product_id')

            ->select(
                'p.product_name as product',
                'u.name as unit',

                DB::raw('COALESCE(req.ordered, 0) as ordered'),
                DB::raw('COALESCE(im_out.delivered, 0) as actual_deliver'),
                DB::raw('COALESCE(last_im.ending_balance, 0) as ending_balance')
            )

            ->whereRaw('COALESCE(req.ordered, 0) > 0') // ✅ ADD HERE
            ->orderBy('p.product_name', 'asc')
            ->get();

        return response()->json($report);
    }


    public function exportExcel(Request $request)
    {
        $report = $this->inventory($request)->getData();

        $filename = 'Inventory_Report_' . now()->format('Ymd_His') . '.xlsx';

        // ✅ LOG EXPORT
        ReportExportLog::create([
            'exported_by' => Auth::id(),
            'export_format' => 'EXCEL',
            'text' =>  $filename,
        ]);

        return Excel::download(
            new InventoryReportExport($report),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $report = $this->inventory($request)->getData();

        $pdf = Pdf::loadView('reports.inventory_pdf', [
            'data' => $report,
            'date' => now()->format('F d, Y'),
        ])->setPaper('a4', 'portrait');

        // ✅ LOG EXPORT

        $filename =  'Inventory_Report_' . now()->format('Ymd_His') . '.pdf';

        ReportExportLog::create([
            'exported_by' => Auth::id(),
            'export_format' => 'PDF',
            'text' =>  $filename,
        ]);

        return $pdf->download(
           $filename
        );
    }

}
