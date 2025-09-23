<?php

namespace Opscale\NovaWebhooks\Observers;

use Illuminate\Database\Eloquent\Model;
use Opscale\NovaWebhooks\Events\WebhookTriggered;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;

class WebhookObserver
{
    final public function created(Model $model): void
    {
        WebhookTriggered::dispatch($model, WebhookAction::CREATE->value);
    }

    final public function updated(Model $model): void
    {
        WebhookTriggered::dispatch($model, WebhookAction::UPDATE->value);
    }

    final public function deleted(Model $model): void
    {
        WebhookTriggered::dispatch($model, WebhookAction::DELETE->value);
    }
}
