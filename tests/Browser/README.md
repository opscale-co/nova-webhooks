# Browser Tests

This directory contains browser/end-to-end tests for the Nova Webhooks package.

## Structure

- `Components/` - Tests for individual Nova components (fields, actions, etc.)
- `Pages/` - Tests for complete page workflows

## Setup

Browser tests would typically use Laravel Dusk or similar tools to test the Nova interface.

Example test structure:

```php
<?php

namespace Opscale\NovaWebhooks\Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Opscale\NovaWebhooks\Tests\DuskTestCase;

class WebhookManagementTest extends DuskTestCase
{
    public function test_can_create_webhook_through_nova_interface()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user())
                    ->visit('/nova/resources/webhooks/new')
                    ->type('name', 'Test Webhook')
                    ->type('url', 'https://example.com/webhook')
                    ->select('resource', 'App\\Models\\User')
                    ->select('action', 'create')
                    ->press('Create Webhook')
                    ->assertPathIs('/nova/resources/webhooks/*');
        });
    }
}
```

## Components to Test

- Webhook Resource (CRUD operations)
- Webhook form validation
- Resource dropdown population
- Action dropdown functionality
- Header key-value input
- Enable/disable toggle

## Page Workflows to Test

- Creating a new webhook
- Editing an existing webhook
- Deleting a webhook
- Bulk operations
- Filtering and searching
- Resource detail view