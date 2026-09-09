<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Models\Concerns\HasPhoneNumber;
use Botble\Ecommerce\Traits\LocationTrait;

class Address extends BaseModel
{
    use HasPhoneNumber;
    use LocationTrait;

    protected $table = 'ec_customer_addresses';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'country',
        'state',
        'city',
        'address',
        'zip_code',
        'customer_id',
        'is_default',
    ];
}
