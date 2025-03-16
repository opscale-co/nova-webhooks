<?php

namespace Opscale\NovaWebhooks\Observers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Opscale\NovaWebhooks\Enums\WebhookAction;
use Opscale\NovaWebhooks\Models\Webhook;
use Spatie\WebhookServer\WebhookCall;

class WebhookObserver
{
    public function created(Model $model)
    {
        $this->fireWebhook($model, WebhookAction::CREATE->value);
    }

    public function updated(Model $model)
    {
        $this->fireWebhook($model, WebhookAction::UPDATE->value);
    }

    public function deleted(Model $model)
    {
        $this->fireWebhook($model, WebhookAction::DELETE->value);
    }

    private function fireWebhook($model, $action)
    {
        try {
            $resource = class_basename(get_class($model));

            $webhook = Webhook::where('resource', $resource)
                ->where('action', $action)
                ->first();

            if ($webhook != null) {
                WebhookCall::create()
                    ->url($webhook->url)
                    ->useHttpVerb('POST')
                    ->withHeaders($webhook->headers)
                    ->payload($model->toArray())
                    ->doNotSign()
                    ->dispatch();
            }
        } catch (Exception $e) {

        }
    }
}
