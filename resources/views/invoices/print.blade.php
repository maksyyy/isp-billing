<!DOCTYPE html>
<html>
<head>
    <meta name="robots" content="noindex, nofollow">
    <title>Cetak Invoice</title>
    <style>
        body { font-family: Arial; }
        .card {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body onload="window.print()">

@foreach($invoices as $i)
<div class="card">
    <h3>Invoice</h3>
    <p>Customer: {{ $i->customer->name }}</p>
    <p>Jumlah: Rp {{ number_format($i->amount) }}</p>
    <p>Dibayar: Rp {{ number_format($i->paid_amount ?? 0) }}</p>
    <p>Status: {{ $i->status }}</p>
    <p>Jatuh Tempo: {{ $i->due_date }}</p>
</div>
@endforeach

</body>
</html>