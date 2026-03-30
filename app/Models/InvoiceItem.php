<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'name',
        'qty',
        'duration_min',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'qty' => 'integer',
        'duration_min' => 'integer',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the invoice for this item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the service for this item (if still exists).
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
