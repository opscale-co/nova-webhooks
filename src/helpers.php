<?php

if (! function_exists('loadedWebhookableResources')) {
    function loadedWebhookableResources(
        Laravel\Nova\Http\Requests\NovaRequest $request)
    {
        // To avoid Fully Qualified Class Names should only be used for accessing class names
        // linting error, we need to store the class names in variables.
        $novaClass = \Laravel\Nova\Nova::class;
        $resourceClass = \Laravel\Nova\Resource::class;
        $webhookableClass = \Opscale\NovaWebhooks\Traits\Webhookable::class;

        return collect($novaClass::availableResources($request))
            ->filter(function ($resource) {
                return is_a($resource, $resourceClass, true);
            })
            ->filter(function ($resource) {
                return in_array(
                    $webhookableClass,
                    class_uses_recursive($resource::$model));
            });
    }
}
