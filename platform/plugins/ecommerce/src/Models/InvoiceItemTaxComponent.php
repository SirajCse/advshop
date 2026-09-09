<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItemTaxComponent extends BaseModel
{
    protected $table = 'ec_invoice_item_tax_components';

    protected $fillable = [
        'invoice_item_id',
        'name',
        'code',
        'rate',
        'amount',
        'jurisdiction',
        'metadata',
    ];

    protected $casts = [
        'rate' => 'float',
        'amount' => 'float',
        'metadata' => 'json',
    ];

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }
}
