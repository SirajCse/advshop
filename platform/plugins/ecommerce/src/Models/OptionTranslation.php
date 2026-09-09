<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionTranslation extends BaseModel
{
    protected $table = 'ec_options_translations';

    public $timestamps = false;

    protected $fillable = [
        'lang_code',
        'ec_options_id',
        'name',
    ];

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'ec_options_id');
    }
}
