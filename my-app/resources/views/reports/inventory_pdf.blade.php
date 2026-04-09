<style>
    body { font-family: DejaVu Sans; font-size: 12px; }
    .header { text-align: center; margin-bottom: 10px; }
    .title { font-size: 16px; font-weight: bold; }
    .sub { font-size: 12px; color: #555; }

    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f3f4f6; }
    .text-right { text-align: right; }

    .total { font-weight: bold; background: #f9fafb; }

    /* 🔥 NEW */
    .category-row {
        font-weight: bold;
        background: #e5e7eb;
    }
    .month-row {
    background: #d1d5db;
    font-weight: bold;
    }
</style>

<div class="header">
    <div class="title">Inventory Report</div>
    <div class="sub">Generated: {{ $date }}</div>
</div>

<table>
    <thead>
        <tr>
            <th>PRODUCTS</th>
            <th>UNIT</th>
            <th>ORDERED</th>
            <th>DELIVERED</th>
            <th>ADJUSTMENT</th>
            <th>ENDING</th>
        </tr>
    </thead>
<tbody>

@php
    $totalOrdered = 0;
    $totalDelivered = 0;
    $totalAdjustment = 0;
    $totalBalance = 0;
@endphp

@if(isset($type) && $type === 'MONTHLY')

    @foreach($data as $monthData)

        @php
            $monthItems = collect($monthData['data']);
            $grouped = $monthItems->groupBy('category');

            $monthOrdered = 0;
            $monthDelivered = 0;
            $monthAdjustment = 0;
            $monthBalance = 0;
        @endphp

        <!-- 🔷 MONTH HEADER -->
        <tr style="background:#d1d5db; font-weight:bold;">
            <td colspan="6">{{ strtoupper($monthData['month']) }}</td>
        </tr>

        @foreach($grouped as $category => $items)

            <!-- 🔹 CATEGORY HEADER -->
            <tr class="category-row">
                <td colspan="6">{{ strtoupper($category ?? 'UNCATEGORIZED') }}</td>
            </tr>

            @foreach($items as $item)
            <tr>
                <td>{{ $item->product }}</td>
                <td>{{ $item->unit }}</td>
                <td class="text-right">{{ $item->ordered }}</td>
                <td class="text-right">{{ $item->delivered }}</td>
                <td class="text-right">{{ $item->adjustment }}</td>
                <td class="text-right">{{ $item->ending }}</td>
            </tr>

            @php
                $monthOrdered += $item->ordered;
                $monthDelivered += $item->delivered;
                $monthAdjustment += $item->adjustment;
                $monthBalance += $item->ending;

                $totalOrdered += $item->ordered;
                $totalDelivered += $item->delivered;
                $totalAdjustment += $item->adjustment;
                $totalBalance += $item->ending;
            @endphp
            @endforeach

        @endforeach

        <!-- 🔥 MONTH TOTAL -->
        <tr class="total">
            <td>TOTAL {{ strtoupper($monthData['month']) }}</td>
            <td></td>
            <td class="text-right">{{ $monthOrdered }}</td>
            <td class="text-right">{{ $monthDelivered }}</td>
            <td class="text-right">{{ $monthAdjustment }}</td>
            <td class="text-right">{{ $monthBalance }}</td>
        </tr>

        <!-- spacing -->
        <tr><td colspan="6" style="padding:4px;"></td></tr>

    @endforeach

@else

    {{-- ✅ SUMMARY MODE (your original) --}}
    @php
        $grouped = collect($data)->groupBy('category');
    @endphp

    @foreach($grouped as $category => $items)

        <tr class="category-row">
            <td colspan="6">{{ strtoupper($category ?? 'UNCATEGORIZED') }}</td>
        </tr>

        @foreach($items as $item)
        <tr>
            <td>{{ $item->product }}</td>
            <td>{{ $item->unit }}</td>
            <td class="text-right">{{ $item->ordered }}</td>
            <td class="text-right">{{ $item->delivered }}</td>
            <td class="text-right">{{ $item->adjustment }}</td>
            <td class="text-right">{{ $item->ending }}</td>
        </tr>

        @php
            $totalOrdered += $item->ordered;
            $totalDelivered += $item->delivered;
            $totalAdjustment += $item->adjustment;
            $totalBalance += $item->ending;
        @endphp
        @endforeach

    @endforeach

@endif

<!-- 🔥 GRAND TOTAL -->
<tr class="total">
    <td>GRAND TOTAL</td>
    <td></td>
    <td class="text-right">{{ $totalOrdered }}</td>
    <td class="text-right">{{ $totalDelivered }}</td>
    <td class="text-right">{{ $totalAdjustment }}</td>
    <td class="text-right">{{ $totalBalance }}</td>
</tr>

</tbody>
</table>