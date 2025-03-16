<?php

namespace Opscale\NovaWebhooks\Nova;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaWebhooks\Enums\WebhookAction;
use Opscale\NovaWebhooks\Models\Webhook as Model;
use Opscale\NovaWebhooks\Traits\Webhookable;

class Webhook extends Resource
{
    public static $model = Model::class;

    public static $title = 'name';

    public static $search = [
        'name',
    ];

    public static function label()
    {
        return _('Webhooks');
    }

    public static function singularLabel()
    {
        return _('Webhook');
    }

    public function fields(NovaRequest $request)
    {
        // Get all resources that use the Webhookable trait
        $resources = loadedWebhookableResources($request)
            ->mapWithKeys(function ($resource) {
                return [class_basename($resource) => $resource::singularLabel()];
            });

        return [
            Text::make(_('Name'), 'name')
                ->rules(Model::rules('name'))
                ->sortable(),

            Textarea::make(_('Description'), 'description')
                ->rules(Model::rules('description')),

            Text::make(_('URL'), 'url')
                ->rules(Model::rules('url'))
                ->hideFromIndex(),

            KeyValue::make(_('Headers'), 'headers')
                ->keyLabel(_('Header'))
                ->valueLabel(_('Value'))
                ->actionText(_('Add header')),

            Select::make(_('Resource'), 'resource')
                ->options($resources)
                ->displayUsingLabels()
                ->rules(Model::rules('resource'))
                ->sortable()
                ->filterable()
                ->help(_('Only models using Webhookable trait are available.')),

            Select::make(_('Action'), 'action')
                ->options(collect(WebhookAction::cases())
                    ->mapWithKeys(function ($item, $key) {
                        return [$item->value => _($item->value)];
                    }))
                ->displayUsingLabels()
                ->rules(Model::rules('action'))
                ->sortable()
                ->filterable(),

            Boolean::make(_('Enabled'), 'enabled')
                ->sortable()
                ->filterable()
                ->hideWhenCreating(),
        ];
    }
}
