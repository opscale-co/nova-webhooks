<?php

namespace Opscale\NovaWebhooks;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaWebhooks\Nova\Webhook;
use Override;

class Tool extends NovaTool
{
    #[Override]
    final public function boot(): void
    {
        parent::boot();

        Nova::script('nova-webhooks', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-webhooks', __DIR__ . '/../dist/css/tool.css');
    }

    #[Override]
    final public function menu(Request $request): MenuItem
    {
        return MenuItem::resource(Webhook::class);
    }
}
