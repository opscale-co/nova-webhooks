<?php

namespace Opscale\NovaWebhooks\Traits;

use Opscale\NovaWebhooks\Observers\WebhookObserver;

trait Webhookable
{
    public static function bootWebhookable()
    {
        static::observe(WebhookObserver::class);
    }
}
