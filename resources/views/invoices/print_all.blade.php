<!DOCTYPE html>
<html>
<head>
    <meta name="robots" content="noindex, nofollow">
    <title>Cetak Semua Invoice</title>
</head>
<body onload="window.print()">

@foreach($invoices as $invoice)
    <div style="margin-bottom:50px;">
        <h3>Invoice</h3>
        <p>Nama: {{ $invoice->customer->name }}</p>
        <p>Jumlah: Rp {{ number_format($invoice->amount) }}</p>
        <p>Jatuh Tempo: {{ $invoice->due_date }}</p>
        <p>Status: {{ $invoice->status }}</p>
        <hr>
    </div>
@endforeach

</body>
</html>