<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProductTaxComponent extends BaseModel
{
    protected $table = 'ec_order_product_tax_components';

    protected $fillable = [
        'order_product_id',
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

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class);
    }
}
