<?php

namespace Opscale\NovaWebhooks\Models\Enums;

enum WebhookAction: string
{
    case CREATE = 'Create';
    case UPDATE = 'Update';
    case DELETE = 'Delete';
}
