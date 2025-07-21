<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request for Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        .logo { width: 80px; height: 80px; object-fit: contain; }
        .header, .footer { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="text-center header">
        <div style="display: flex; align-items: center; justify-content: center;">
            <img src="{{ public_path('storage/app-settings/app-logo.png') }}" class="logo" alt="Logo">
            <div>
                <h2 class="font-bold">Pangasinan I Electric Cooperative (PANELCO I)</h2>
                <p>San Jose, Bani, Pangasinan</p>
                <p>T/F: +6375-551-5564 | E: panelco_one@yahoo.com | W: www.panelco1.com.ph</p>
            </div>
        </div>
    </div>

    <h3 class="text-center font-bold">REQUEST FOR QUOTATION</h3>

    <div class="grid" style="margin-bottom: 20px;">
        <div>
            <p><strong>TO:</strong></p>
            <p><strong>ATTENTION:</strong></p>
            <p><strong>Tel. No:</strong></p>
            <p><strong>Fax No:</strong></p>
            <p><strong>Mobile No:</strong></p>
            <p><strong>Email:</strong></p>
        </div>
        <div class="text-right">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($rfq->created_at)->format('F d, Y') }}</p>
            <p><strong>RFQ No:</strong> {{ $rfq->rfq_no }}</p>
            <p><strong>PR No:</strong> {{ $rfq->pr?->pr_no }}</p>
        </div>
    </div>

    <p>
        Kindly quote your best price for the following items:<br>
        Please indicate brand, availability, credit terms, delivery schedule and warranty.<br>
        Reference this RFQ in your Quotation, preferably with your own Quotation Reference No.
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Material Description</th>
                <th>Qty</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rfq->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer" style="margin-top: 30px;">
        <p><strong>Send your quotation or clarifications to:</strong></p>
        <p>Mr. Eleaser C. Ramos</p>
        <p>Purchaser</p>
        <p>Mobile: 09198394643</p>
        <p>Email: ramos.eleaser@gmail.com</p>
    </div>
</body>
</html>
