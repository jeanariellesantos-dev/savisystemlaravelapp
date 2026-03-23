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
            <th>ACTUAL DELIVER</th>
            <th>ENDING BALANCE</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalOrdered = 0;
            $totalDelivered = 0;
            $totalBalance = 0;
        @endphp

        @foreach($data as $item)
        <tr>
            <td>{{ $item->product }}</td>
            <td>{{ $item->unit }}</td>
            <td class="text-right">{{ $item->ordered }}</td>
            <td class="text-right">{{ $item->actual_deliver }}</td>
            <td class="text-right">{{ $item->ending_balance }}</td>
        </tr>

        @php
            $totalOrdered += $item->ordered;
            $totalDelivered += $item->actual_deliver;
            $totalBalance += $item->ending_balance;
        @endphp
        @endforeach

        <tr class="total">
            <td>TOTAL</td>
            <td></td>
            <td class="text-right">{{ $totalOrdered }}</td>
            <td class="text-right">{{ $totalDelivered }}</td>
            <td class="text-right">{{ $totalBalance }}</td>
        </tr>
    </tbody>
</table>