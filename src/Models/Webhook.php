<?php

namespace Opscale\NovaWebhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Laravel\Nova\Actions\Actionable;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Opscale\Validations\Validatable;

class Webhook extends Model
{
    use Actionable;
    use Validatable;

    public $casts = [
        'headers' => 'array',
        'action' => WebhookAction::class,
        'enabled' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'description',
        'url',
        'headers',
        'resource',
        'action',
        'enabled',
    ];

    /**
     * @return array<string, mixed>
     */
    final public function validationRules(): array
    {
        return [
            'name' => ['required'],
            'description' => ['required', 'max:512'],
            'url' => ['required', 'url'],
            'headers' => ['nullable', 'json'],
            'resource' => ['required'],
            'action' => ['required', Rule::enum(WebhookAction::class)],
        ];
    }
}
