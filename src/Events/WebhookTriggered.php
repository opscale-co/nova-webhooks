<?php

namespace Opscale\NovaWebhooks\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class WebhookTriggered
{
    use Dispatchable;

    /**
     * @param  Model  $model  The model instance that changed
     * @param  string  $action  The CRUD action that occurred
     */
    final public function __construct(
        public Model $model,
        public string $action
    ) {}
}
