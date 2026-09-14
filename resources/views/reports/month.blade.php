<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Expense report — {{ $month->format('F Y') }}</title>
<style>
    @media print {
        @page { margin: 24px; }
    }
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #1d2420; font-size: 13px; }
    h1 { font-size: 20px; margin-bottom: 2px; }
    .org { color: #565f57; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    th, td { text-align: left; padding: 6px 10px; border-bottom: 1px solid #ddd; }
    th { background: #f0efe9; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #565f57; }
    .totals td { font-weight: bold; border-top: 2px solid #333; }
    .negative { color: #b23b3b; }
</style>
</head>
<body>
    <h1>Expense report — {{ $month->format('F Y') }}</h1>
    <div class="org">{{ $organization->name }}</div>

    <table>
        <tr><th>Total income</th><td>{{ number_format($summary['total_income'], 2) }}</td></tr>
        <tr><th>Total allocated</th><td>{{ number_format($summary['total_allocated'], 2) }}</td></tr>
        <tr><th>Unallocated income</th><td>{{ number_format($summary['unallocated_income'], 2) }}</td></tr>
        <tr><th>Total spent</th><td>{{ number_format($summary['total_spent'], 2) }}</td></tr>
        <tr><th>Net</th><td class="{{ $summary['net'] < 0 ? 'negative' : '' }}">{{ number_format($summary['net'], 2) }}</td></tr>
    </table>

    <table>
        <thead>
            <tr><th>Category</th><th>Allocated</th><th>Spent</th><th>Remaining</th></tr>
        </thead>
        <tbody>
            @foreach ($summary['envelopes'] as $envelope)
                <tr>
                    <td>{{ $envelope['category_name'] }}</td>
                    <td>{{ number_format($envelope['allocated'], 2) }}</td>
                    <td>{{ number_format($envelope['spent'], 2) }}</td>
                    <td class="{{ $envelope['remaining'] < 0 ? 'negative' : '' }}">{{ number_format($envelope['remaining'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
