<?php

namespace Botble\Language\Events;

use Botble\Language\Models\Language;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LanguageCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Language $language)
    {
    }
}
