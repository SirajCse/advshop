<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionValueTranslation extends BaseModel
{
    protected $table = 'ec_option_value_translations';

    public $timestamps = false;

    protected $fillable = [
        'lang_code',
        'ec_option_value_id',
        'option_value',
    ];

    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(OptionValue::class, 'ec_option_value_id');
    }
}
