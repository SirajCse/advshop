<?php

namespace Botble\Contact\Providers;

use Botble\Contact\Events\SentContactEvent;
use Botble\Contact\Listeners\SendContactEmailListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SentContactEvent::class => [
            SendContactEmailListener::class,
        ],
    ];
}
