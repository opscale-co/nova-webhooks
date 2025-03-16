<?php

namespace Opscale\NovaWebhooks\Enums;

enum WebhookAction: string
{
    case CREATE = 'Create';
    case UPDATE = 'Update';
    case DELETE = 'Delete';
}
