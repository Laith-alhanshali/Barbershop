<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin.invoices.print.title') }} - {{ $invoice->number }}</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            color: #1f2937;
            margin: 0;
            padding: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 1rem;
        }

        .logo h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .invoice-details {
            text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }};
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            background-color: #f3f4f6;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-unpaid { background-color: #fef3c7; color: #92400e; }
        .status-void { background-color: #f3f4f6; color: #374151; }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .meta-box h3 {
            font-size: 0.875rem;
            text-transform: uppercase;
            color: #6b7280;
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        .meta-box p {
            margin: 0;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        th {
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            padding: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.875rem;
            text-transform: uppercase;
            color: #6b7280;
        }

        td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .text-right { text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

        .totals {
            margin-left: auto;
            width: 300px;
        }
        
        [dir="rtl"] .totals {
            margin-left: 0;
            margin-right: auto;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .total-row.final {
            border-bottom: none;
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 0.5rem;
            padding-top: 1rem;
            border-top: 2px solid #e5e7eb;
        }

        .actions {
            position: fixed;
            top: 1rem;
            {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            background-color: #1f2937;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .btn:hover { background-color: #374151; }
        .btn-outline { background-color: transparent; border: 1px solid #d1d5db; color: #374151; }
        .btn-outline:hover { background-color: #f3f4f6; }

        @media print {
            .actions { display: none; }
            body { padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()" class="btn">{{ __('admin.invoices.print.print_button') }}</button>
        <button onclick="window.close()" class="btn btn-outline">{{ __('admin.invoices.print.close_button') }}</button>
    </div>

    <div class="header">
        <div class="logo">
            <h1>Barber Shop</h1>
        </div>
        <div class="invoice-details">
            <h2 style="margin: 0 0 0.5rem 0;">#{{ $invoice->number }}</h2>
            <span class="status-badge status-{{ $invoice->status }}">
                {{ __('admin.invoices.status.' . $invoice->status) }}
            </span>
            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">
                {{ $invoice->created_at->format('Y-m-d') }}
            </p>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-box">
            <h3>{{ __('admin.invoices.fields.customer') }}</h3>
            <p>{{ $invoice->customer->name }}</p>
            <p style="color: #6b7280; font-size: 0.875rem;">{{ $invoice->customer->phone }}</p>
        </div>
        <div class="meta-box">
            <h3>{{ __('admin.invoices.fields.barber') }}</h3>
            <p>{{ $invoice->barber->name }}</p>
        </div>
        <div class="meta-box">
            <h3>{{ __('admin.invoices.print.appointment_date') }}</h3>
            <p>{{ $invoice->appointment->start_at->format('Y-m-d h:i A') }}</p>
        </div>
        <div class="meta-box">
            <h3>{{ __('admin.invoices.fields.payment_method') }}</h3>
            <p>
                {{ $invoice->payment_method 
                    ? __('admin.invoices.payment_methods.' . $invoice->payment_method) 
                    : '-' }}
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('admin.invoices.items.fields.name') }}</th>
                <th>{{ __('admin.invoices.items.fields.duration_min') }}</th>
                <th>{{ __('admin.invoices.items.fields.qty') }}</th>
                <th>{{ __('admin.invoices.items.fields.unit_price') }}</th>
                <th class="text-right">{{ __('admin.invoices.items.fields.line_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->duration_min }} min</td>
                <td>{{ $item->qty }}</td>
                <td>{{ number_format($item->unit_price, 2) }} SAR</td>
                <td class="text-right font-mono">{{ number_format($item->line_total, 2) }} SAR</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span>{{ __('admin.invoices.fields.subtotal') }}</span>
            <span class="font-mono">{{ number_format($invoice->subtotal, 2) }} SAR</span>
        </div>
        <div class="total-row">
            <span>{{ __('admin.invoices.fields.discount') }}</span>
            <span class="font-mono">-{{ number_format($invoice->discount, 2) }} SAR</span>
        </div>
        <div class="total-row">
            <span>{{ __('admin.invoices.fields.tax') }}</span>
            <span class="font-mono">{{ number_format($invoice->tax, 2) }} SAR</span>
        </div>
        <div class="total-row final">
            <span>{{ __('admin.invoices.fields.total') }}</span>
            <span class="font-mono">{{ number_format($invoice->total, 2) }} SAR</span>
        </div>
    </div>
</body>
</html>
