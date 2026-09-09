<?php

namespace Botble\Marketplace\Forms;

use Botble\Marketplace\Forms\Fields\CustomEditorField;
use Botble\Marketplace\Http\Requests\Fronts\VendorStoreRequest;

class VendorStoreForm extends StoreForm
{
    public function setup(): void
    {
        parent::setup();

        // vacation_mode + vacation_message are defined in the parent StoreForm,
        // so both admin and vendor dashboards share the same fields.
        $this
            ->setValidatorClass(VendorStoreRequest::class)
            ->modify('content', CustomEditorField::class)
            ->remove(['status', 'customer_id']);
    }
}
