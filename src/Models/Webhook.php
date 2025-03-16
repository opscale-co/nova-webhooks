<?php

namespace Opscale\NovaWebhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Enum;
use Laravel\Nova\Actions\Actionable;
use Opscale\NovaWebhooks\Enums\WebhookAction;

class Webhook extends Model
{
    use Actionable;

    public $casts = [
        'headers' => 'array',
        'action' => WebhookAction::class,
    ];

    protected static function rules()
    {
        return [
            'name' => ['required'],
            'description' => ['nullable', 'max:512'],
            'url' => ['required', 'url'],
            'headers' => ['nullable', 'json'],
            'resource' => ['required'],
            'action' => ['required', new Enum(WebhookAction::class)],
        ];
    }
}
