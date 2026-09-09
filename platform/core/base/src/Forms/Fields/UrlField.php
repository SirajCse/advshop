<?php

namespace Botble\Base\Forms\Fields;

class UrlField extends TextField
{
    protected function getTemplate(): string
    {
        return 'core/base::forms.fields.url';
    }
}
