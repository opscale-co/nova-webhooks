<?php

namespace Opscale\NovaWebhooks\Concerns;

use Opscale\NovaWebhooks\Observers\WebhookObserver;

/**
 * Trait to be used by external models to enable webhook functionality.
 * This trait is part of the public API and is intended for external use.
 *
 * @phpstan-ignore-next-line
 */
trait Webhookable
{
    final public static function bootWebhookable(): void
    {
        static::observe(WebhookObserver::class);
    }
}
