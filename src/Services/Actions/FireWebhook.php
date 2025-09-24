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
    final public function fireWebhook(string $resource, string $action, Model $model): void
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

            /** @var array<string, string>|null $headersData */
            $headersData = $webhook->getAttribute('headers');
            $headers = is_array($headersData) ? $headersData : [];

            $webhookCall = WebhookCall::create()
                ->url($url)
                ->useHttpVerb('POST')
                ->payload([
                    'resource' => $resource,
                    'event' => $action,
                    'data' => $model->toArray(),
                    'timestamp' => Carbon::now()->toIso8601String(),
                ])
                ->doNotSign();

            if ($headers !== []) {
                $webhookCall->withHeaders($headers);
            }

            $webhookCall->dispatch();
        }
    }

    /**
     * Handle the action as an event listener (Laravel's default event method).
     */
    final public function handle(WebhookTriggered $webhookTriggered): void
    {
        $this->fireWebhook(
            $webhookTriggered->model::class,
            $webhookTriggered->action,
            $webhookTriggered->model
        );
    }

    /**
     * Handle the action as an event listener (explicit method).
     */
    final public function asListener(WebhookTriggered $webhookTriggered): void
    {
        $this->fireWebhook(
            $webhookTriggered->model::class,
            $webhookTriggered->action,
            $webhookTriggered->model
        );
    }
}
