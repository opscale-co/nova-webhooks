<?php

namespace Opscale\NovaWebhooks\Tests\Feature\Models;

use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Opscale\NovaWebhooks\Models\Webhook;
use Opscale\NovaWebhooks\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Webhook::class)]
final class WebhookTest extends TestCase
{
    #[Test]
    final public function it_can_create_a_webhook_with_valid_data(): void
    {
        // Arrange
        $data = [
            'name' => 'Test Webhook',
            'description' => 'A test webhook for testing purposes',
            'url' => 'https://example.com/webhook',
            'headers' => ['Authorization' => 'Bearer token'],
            'resource' => 'App\\Models\\User',
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ];

        // Act
        $webhook = Webhook::create($data);

        // Assert
        $this->assertInstanceOf(Webhook::class, $webhook);
        $this->assertDatabaseHas('webhooks', [
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => 'App\\Models\\User',
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);
    }

    #[Test]
    final public function it_casts_action_to_webhook_action_enum(): void
    {
        // Arrange & Act
        $webhook = Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => 'App\\Models\\User',
            'action' => WebhookAction::UPDATE->value,
        ]);

        // Assert
        $this->assertInstanceOf(WebhookAction::class, $webhook->action);
        $this->assertSame(WebhookAction::UPDATE, $webhook->action);
    }

    #[Test]
    final public function it_casts_headers_to_array(): void
    {
        // Arrange
        $headers = ['Authorization' => 'Bearer token', 'Content-Type' => 'application/json'];

        // Act
        $webhook = Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => 'App\\Models\\User',
            'action' => WebhookAction::CREATE->value,
            'headers' => $headers,
        ]);

        // Assert
        $this->assertIsArray($webhook->headers);
        $this->assertSame($headers, $webhook->headers);
    }

    #[Test]
    final public function it_has_validation_rules(): void
    {
        // Arrange
        $webhook = new Webhook;

        // Act
        $rules = $webhook->validationRules();

        // Assert
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('url', $rules);
        $this->assertArrayHasKey('resource', $rules);
        $this->assertArrayHasKey('action', $rules);

        // Check specific rules
        $this->assertContains('required', $rules['name']);
        $this->assertContains('required', $rules['url']);
        $this->assertContains('url', $rules['url']);
        $this->assertContains('required', $rules['resource']);
        $this->assertContains('required', $rules['action']);
    }

    #[Test]
    final public function it_validates_required_fields(): void
    {
        // Arrange
        $webhook = new Webhook;
        $rules = $webhook->validationRules();

        // Assert required fields
        $requiredFields = ['name', 'url', 'resource', 'action'];

        foreach ($requiredFields as $requiredField) {
            $this->assertArrayHasKey($requiredField, $rules);
            $this->assertContains('required', $rules[$requiredField]);
        }
    }

    #[Test]
    final public function it_validates_url_format(): void
    {
        // Arrange
        $webhook = new Webhook;
        $rules = $webhook->validationRules();

        // Assert
        $this->assertContains('url', $rules['url']);
    }

    #[Test]
    final public function it_validates_optional_fields(): void
    {
        // Arrange
        $webhook = new Webhook;
        $rules = $webhook->validationRules();

        // Assert
        $this->assertArrayHasKey('description', $rules);
        $this->assertContains('nullable', $rules['description']);
        $this->assertContains('max:512', $rules['description']);

        $this->assertArrayHasKey('headers', $rules);
        $this->assertContains('nullable', $rules['headers']);
    }

    #[Test]
    final public function it_can_be_enabled_and_disabled(): void
    {
        // Arrange
        $webhook = Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => 'App\\Models\\User',
            'action' => WebhookAction::CREATE->value,
            'enabled' => false,
        ]);

        // Act & Assert - Initially disabled
        $this->assertFalse($webhook->enabled);

        // Act - Enable webhook
        $webhook->update(['enabled' => true]);

        // Assert - Now enabled
        $this->assertTrue($webhook->fresh()->enabled);
    }

    #[Test]
    final public function it_can_store_multiple_webhooks_for_same_resource_and_action(): void
    {
        // Arrange & Act
        $webhook1 = Webhook::create([
            'name' => 'First Webhook',
            'url' => 'https://first.example.com/webhook',
            'resource' => 'App\\Models\\User',
            'action' => WebhookAction::CREATE->value,
        ]);

        $webhook2 = Webhook::create([
            'name' => 'Second Webhook',
            'url' => 'https://second.example.com/webhook',
            'resource' => 'App\\Models\\User',
            'action' => WebhookAction::CREATE->value,
        ]);

        // Assert
        $this->assertNotEquals($webhook1->id, $webhook2->id);
        $this->assertEquals(2, Webhook::where('resource', 'App\\Models\\User')
            ->where('action', WebhookAction::CREATE->value)
            ->count());
    }
}
