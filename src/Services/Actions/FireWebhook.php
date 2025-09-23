<?php

namespace Opscale\NovaWebhooks\Services\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Opscale\NovaWebhooks\Events\WebhookTriggered;
use Opscale\NovaWebhooks\Models\Webhook;
use Spatie\WebhookServer\WebhookCall;

final class FireWebhook
{
    use AsAction;

    /**
     * Execute the webhook firing action.
     *
     * @param  class-string<Model>  $resource
     */
    final public function handle(string $resource, string $action, Model $model): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Webhook> $webhooks */
        $webhooks = Webhook::query()
            ->where('resource', $resource)
            ->where('action', $action)
            ->where('enabled', true)
            ->get();

        foreach ($webhooks as $webhook) {
            /** @var string $url */
            $url = $webhook->getAttribute('url');

            /** @var array<string, string> $headers */
            $headers = $webhook->getAttribute('headers') ?? [];

            WebhookCall::create()
                ->url($url)
                ->useHttpVerb('POST')
                ->withHeaders($headers)
                ->payload([
                    'resource' => $resource,
                    'event' => $action,
                    'data' => $model->toArray(),
                    'timestamp' => Carbon::now()->toIso8601String(),
                ])
                ->doNotSign()
                ->dispatch();
        }
    }

    /**
     * Handle the action as an event listener.
     */
    final public function asListener(WebhookTriggered $webhookTriggered): void
    {
        $this->handle(
            $webhookTriggered->model::class,
            $webhookTriggered->action,
            $webhookTriggered->model
        );
    }
}
