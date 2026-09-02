<?php

namespace Opscale\NovaWebhooks\Nova;

use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Opscale\NovaWebhooks\Concerns\Webhookable;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Opscale\NovaWebhooks\Models\Webhook as Model;
use Override;

/**
 * @extends resource<Model>
 */
class Webhook extends Resource
{
    /** @var class-string<Model> */
    public static $model = Model::class;

    public static $title = 'name';

    /** @var array<int, string> */
    public static $search = [
        'name',
    ];

    final public static function label(): string
    {
        return __('Webhooks');
    }

    final public static function singularLabel(): string
    {
        return __('Webhook');
    }

    /**
     * @return array<int, Field>
     */
    #[Override]
    final public function fields(NovaRequest $request): array
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules(function (): array {
                    $rules = $this->model()?->validationRules()['name'] ?? [];

                    return is_array($rules) ? $rules : [];
                })
                ->sortable(),

            Textarea::make(__('Description'), 'description')
                ->rules(function (): array {
                    $rules = $this->model()?->validationRules()['description'] ?? [];

                    return is_array($rules) ? $rules : [];
                }),

            Text::make(__('URL'), 'url')
                ->rules(function (): array {
                    $rules = $this->model()?->validationRules()['url'] ?? [];

                    return is_array($rules) ? $rules : [];
                })
                ->hideFromIndex(),

            KeyValue::make(__('Headers'), 'headers')
                ->keyLabel(__('Header'))
                ->valueLabel(__('Value'))
                ->actionText(__('Add header')),

            Select::make(__('Resource'), 'resource')
                ->options($this->getWebhookableResources())
                ->displayUsingLabels()
                ->rules(function (): array {
                    $rules = $this->model()?->validationRules()['resource'] ?? [];

                    return is_array($rules) ? $rules : [];
                })
                ->sortable()
                ->filterable()
                ->help(__('Only models using Webhookable trait are available.')),

            Select::make(__('Action'), 'action')
                ->options($this->getWebhookActions())
                ->displayUsingLabels()
                ->rules(function (): array {
                    $rules = $this->model()?->validationRules()['action'] ?? [];

                    return is_array($rules) ? $rules : [];
                })
                ->sortable()
                ->filterable(),

            Boolean::make(__('Enabled'), 'enabled')
                ->sortable()
                ->filterable()
                ->hideWhenCreating(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getWebhookableResources(): array
    {
        /** @var array<class-string<\Laravel\Nova\Resource<\Illuminate\Database\Eloquent\Model>>, string> $resources */
        $resources = Collection::make(Nova::$resources)
            ->filter(function ($resource): bool {
                return in_array(
                    Webhookable::class,
                    class_uses_recursive($resource::$model));
            })
            ->mapWithKeys(function ($resource): array {
                return [$resource::$model => $resource::singularLabel()];
            })
            ->toArray();

        return $resources;
    }

    /**
     * @return array<string, string>
     */
    private function getWebhookActions(): array
    {
        /** @var array<string, string> */
        $actions = Collection::make(WebhookAction::cases())
            ->mapWithKeys(function (WebhookAction $webhookAction): array {
                return [$webhookAction->value => __($webhookAction->value)];
            })
            ->toArray();

        return $actions;
    }
}
