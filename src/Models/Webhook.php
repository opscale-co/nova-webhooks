<?php

namespace Opscale\NovaWebhooks\Models;

use Enigma\ValidatorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Laravel\Nova\Actions\Actionable;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Override;

class Webhook extends Model
{
    use Actionable;
    use ValidatorTrait;

    public $casts = [
        'headers' => 'array',
        'action' => WebhookAction::class,
    ];

    /**
     * @return array<string, mixed>
     */
    #[Override]
    final public function validationRules(): array
    {
        return [
            'name' => ['required'],
            'description' => ['nullable', 'max:512'],
            'url' => ['required', 'url'],
            'headers' => ['nullable', 'json'],
            'resource' => ['required'],
            'action' => ['required', Rule::enum(WebhookAction::class)],
        ];
    }
}
