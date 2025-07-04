<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Requisition #{{ $record->id }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; margin: 25px; color: #111827; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 26px; }
        .header p { margin: 5px 0; color: #6b7280; }
        .section { margin-bottom: 25px; border: 1px solid #e5e7eb; padding: 20px; border-radius: 8px; }
        .section h2 { margin-top: 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; font-size: 18px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid strong { display: block; margin-bottom: 4px; color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
        th { background-color: #f9fafb; font-weight: 600; }
        .total-row td { font-weight: bold; }
        .text-right { text-align: right; }
        /* --- NEW STYLES FOR THE SIGNATURE AREA --- */
        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .signature-box {
            border-top: 1px solid #333;
            padding-top: 10px;
        }
        .signature-box p {
            margin: 0;
            line-height: 1.5;
        }
        /* --- END NEW STYLES --- */
        .footer { text-align: center; margin-top: 60px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Purchase Requisition</h1>
        <p>Synztec Sdn Bhd</p>
    </div>

    <div class="section">
        <h2>Requisition Information</h2>
        <div class="grid">
            <div><strong>Requisition ID:</strong> #{{ $record->id }}</div>
            <div><strong>Status:</strong> {{ $record->status->value }}</div>
            <div><strong>Requester:</strong> {{ $record->requester->name }}</div>
            <div><strong>Date Created:</strong> {{ $record->created_at->format('d M, Y') }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Vendor & Quotation</h2>
        <div class="grid">
            <div><strong>Vendor:</strong> {{ $record->vendor->name ?? 'N/A' }}</div>
            <div><strong>Quotation #:</strong> {{ $record->quotation_number ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Requested Items</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th class="text-right">Price (MYR)</th>
                    <th class="text-right">Line Total (MYR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4" class="text-right">Grand Total</td>
                    <td class="text-right">MYR {{ number_format($record->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- --- NEW SIGNATURES SECTION --- -->
    <div class="signatures">
        <div class="signature-box">
            <p><strong>Requested by:</strong></p>
            <p style="margin-top: 30px;">_________________________</p>
            <p>{{ $record->requester->name }}</p>
            <p>Date: {{ $record->created_at->format('d M, Y') }}</p>
        </div>
        <div class="signature-box">
            <p><strong>Approved by:</strong></p>
            <p style="margin-top: 30px;">_________________________</p>
            {{-- This part only shows if the requisition has been approved --}}
            @if($record->approver)
                <p>{{ $record->approver->name }}</p>
                <p>Date: {{ $record->approved_at ? $record->approved_at->format('d M, Y') : '' }}</p>
            @else
                <p> </p> {{-- Non-breaking space to maintain height --}}
                <p>Date:</p>
            @endif
        </div>
    </div>
    <!-- --- END SIGNATURES SECTION --- -->

    <div class="footer">
        <p>This is a computer-generated document.</p>
        <p>Generated on: {{ now()->format('d M, Y H:i:s') }}</p>
    </div>

</body>
</html>