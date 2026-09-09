<?php

namespace Botble\Ecommerce\Http\Controllers\Settings;

use Botble\Ecommerce\Forms\Settings\GeneralSettingForm;
use Botble\Ecommerce\Http\Requests\Settings\GeneralSettingRequest;

class GeneralSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/ecommerce::setting.general.name'));

        return GeneralSettingForm::create()->renderForm();
    }

    public function update(GeneralSettingRequest $request)
    {
        $data = $request->validated();

        $emails = [];
        if (! empty($data['admin_notification_email'])) {
            foreach ($data['admin_notification_email'] as $item) {
                $email = $this->extractEmailFromRepeaterItem($item);
                if ($email) {
                    $emails[] = $email;
                }
            }
        }
        $data['admin_notification_email'] = $emails;

        return $this->performUpdate($data);
    }

    protected function extractEmailFromRepeaterItem(mixed $item): ?string
    {
        if (is_string($item) && filter_var($item, FILTER_VALIDATE_EMAIL)) {
            return $item;
        }

        if (is_array($item)) {
            if (isset($item['value']) && is_string($item['value'])) {
                return $item['value'] ?: null;
            }

            foreach ($item as $nested) {
                $result = $this->extractEmailFromRepeaterItem($nested);
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }
}
