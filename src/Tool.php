<?php

namespace Opscale\NovaWebhooks;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;

class Tool extends NovaTool
{
    public function boot()
    {
        Nova::script('nova-webhooks', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-webhooks', __DIR__ . '/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make('NovaWebhooks')
            ->path('nova-webhooks')
            ->icon('server');
    }
}
