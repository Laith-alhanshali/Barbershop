<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoicePrintController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        $invoice->load([
            'customer',
            'barber',
            'appointment',
            'items',
        ]);

        return view('invoices.print', compact('invoice'));
    }
}
