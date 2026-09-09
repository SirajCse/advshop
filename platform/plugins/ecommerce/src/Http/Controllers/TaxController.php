<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\TaxForm;
use Botble\Ecommerce\Http\Requests\TaxRequest;
use Botble\Ecommerce\Models\Tax;
use Botble\Setting\Facades\Setting;
use Illuminate\Http\Request;

class TaxController extends BaseController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(EcommerceHelper::isTaxEnabled(), 404);

            return $next($request);
        });
    }

    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/ecommerce::ecommerce.name'), route('products.index'))
            ->add(trans('plugins/ecommerce::tax.name'), route('tax.index'));
    }

    public function index()
    {
        $this->pageTitle(trans('plugins/ecommerce::tax.name'));

        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/tax.js');

        $with = ['rules'];
        if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
            $with = ['rules.locationCountry', 'rules.locationState', 'rules.locationCity'];
        }

        $taxes = Tax::query()
            ->with($with)
            ->withCount('rules')
            ->orderBy('priority')
            ->orderBy('title')
            ->get();

        return view('plugins/ecommerce::taxes.index', compact('taxes'));
    }

    public function create(Request $request)
    {
        $this->pageTitle(trans('plugins/ecommerce::tax.create'));

        $form = TaxForm::create()->renderForm();

        if ($request->ajax()) {
            return $this
                ->httpResponse()
                ->setData(['html' => $form])
                ->setMessage(PageTitle::getTitle(false));
        }

        return $form;
    }

    public function store(TaxRequest $request)
    {
        $tax = Tax::query()->create($request->input());

        event(new CreatedContentEvent(TAX_MODULE_SCREEN_NAME, $request, $tax));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('tax.index'))
            ->setNextUrl(route('tax.edit', $tax->id))
            ->withCreatedSuccessMessage();
    }

    public function edit(Tax $tax, Request $request)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $tax->title]));

        $form = TaxForm::createFromModel($tax)->renderForm();

        if ($request->ajax()) {
            return $this
                ->httpResponse()
                ->setData(['html' => $form])
                ->setMessage(PageTitle::getTitle(false));
        }

        return $form;
    }

    public function update(Tax $tax, TaxRequest $request)
    {
        $tax->fill($request->input());
        $tax->save();

        event(new UpdatedContentEvent(TAX_MODULE_SCREEN_NAME, $request, $tax));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('tax.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(Tax $tax)
    {
        return DeleteResourceAction::make($tax);
    }

    public function setDefault(Tax $tax)
    {
        Setting::set('ecommerce_default_tax_rate', $tax->id)->save();

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/ecommerce::tax.set_default_success', ['name' => $tax->title]));
    }
}
