<?php

namespace Opscale\NovaWebhooks\Services\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Opscale\Actions\Action;
use Opscale\NovaWebhooks\Events\WebhookTriggered;
use Opscale\NovaWebhooks\Models\Webhook;
use Override;
use Spatie\WebhookServer\WebhookCall;

final class FireWebhook extends Action
{
    /**
     * Get a unique slug identifier for this action.
     */
    #[Override]
    final public function identifier(): string
    {
        return 'fire-webhook';
    }

    /**
     * Get the human-readable name of the action.
     */
    #[Override]
    final public function name(): string
    {
        return __('Fire Webhook');
    }

    /**
     * Get a detailed description of what this action does.
     */
    #[Override]
    final public function description(): string
    {
        return __('Fires webhooks for a specific resource and action, sending the model data to all configured webhook endpoints.');
    }

    /**
     * Define the parameters schema for this action.
     *
     * @return array<int, array{name: string, description: string, type: string, rules: array<int, string>}>
     */
    #[Override]
    final public function parameters(): array
    {
        return [
            [
                'name' => 'resource',
                'description' => __('The fully qualified class name of the resource model'),
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'action',
                'description' => __('The action that triggered the webhook (created, updated, deleted)'),
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'model',
                'description' => __('The Eloquent model instance to include in the webhook payload'),
                'type' => 'object',
                'rules' => ['required'],
            ],
        ];
    }

    /**
     * Execute the action with the given attributes.
     *
     * @param  array{resource: string, action: string, model: Model}  $attributes
     * @return array{dispatched: int}
     */
    #[Override]
    final public function handle(array $attributes = []): array // @phpstan-ignore parameter.defaultValue
    {
        $this->fill($attributes);
        $this->validateAttributes();

        /** @var class-string<Model> $resource */
        $resource = $this->get('resource');

        /** @var string $action */
        $action = $this->get('action');

        /** @var Model $model */
        $model = $this->get('model');

        /** @var \Illuminate\Database\Eloquent\Collection<int, Webhook> $webhooks */
        $webhooks = Webhook::query()
            ->where('resource', $resource)
            ->where('action', $action)
            ->where('enabled', true)
            ->get();

        $dispatched = 0;

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
            $dispatched++;
        }

        return ['dispatched' => $dispatched];
    }

    /**
     * Handle the action as an event listener.
     */
    final public function asListener(WebhookTriggered $webhookTriggered): void
    {
        $this->handle([
            'resource' => $webhookTriggered->model::class,
            'action' => $webhookTriggered->action,
            'model' => $webhookTriggered->model,
        ]);
    }
}
