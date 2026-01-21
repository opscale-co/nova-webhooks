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
    }

    #[Override]
    final public function menu(Request $request): MenuItem
    {
        return MenuItem::resource(Webhook::class);
    }
}
