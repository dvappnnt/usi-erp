<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request for Quotation</title>
</head>
<body>
    <h2>Dear Supplier,</h2>

    <p>Please find attached the Request for Quotation (RFQ) with the number <strong>{{ $rfq->rfq_no }}</strong>.</p>

    <p>
        PR No: <strong>{{ $rfq->pr?->pr_no }}</strong><br>
        Date Issued: <strong>{{ \Carbon\Carbon::parse($rfq->created_at)->format('F d, Y') }}</strong>
    </p>

    <p>Kindly send your quotation as soon as possible. Let us know if you have any questions.</p>

    <br><br>
    <p>Regards,</p>
    <p><strong>PANELCO I</strong></p>
</body>
</html>
