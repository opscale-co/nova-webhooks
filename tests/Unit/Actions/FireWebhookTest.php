<?php

namespace Opscale\NovaWebhooks\Tests\Unit\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Opscale\NovaWebhooks\Events\WebhookTriggered;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Opscale\NovaWebhooks\Models\Webhook;
use Opscale\NovaWebhooks\Services\Actions\FireWebhook;
use Opscale\NovaWebhooks\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FireWebhook::class)]
final class FireWebhookTest extends TestCase
{
    #[Test]
    final public function it_can_fire_webhook_for_enabled_webhooks(): void
    {
        // Arrange
        Bus::fake();
        Carbon::setTestNow('2024-01-01 12:00:00');

        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };
        $model->name = 'Test Model';

        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $model::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
            'headers' => ['Authorization' => 'Bearer token'],
        ]);

        // Act
        $fireWebhook = new FireWebhook;
        $fireWebhook->fireWebhook($model::class, WebhookAction::CREATE->value, $model);

        // Assert
        Bus::assertDispatched(\Spatie\WebhookServer\CallWebhookJob::class, function ($job): bool {
            return $job->webhookUrl === 'https://example.com/webhook';
        });
    }

    #[Test]
    final public function it_does_not_fire_webhook_for_disabled_webhooks(): void
    {
        // Arrange
        Bus::fake();

        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $model::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => false,
        ]);

        // Act
        $fireWebhook = new FireWebhook;
        $fireWebhook->fireWebhook($model::class, WebhookAction::CREATE->value, $model);

        // Assert
        Bus::assertNotDispatched(\Spatie\WebhookServer\CallWebhookJob::class);
    }

    #[Test]
    final public function it_fires_multiple_webhooks_for_same_resource_and_action(): void
    {
        // Arrange
        Bus::fake();

        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        Webhook::create([
            'name' => 'First Webhook',
            'url' => 'https://first.example.com/webhook',
            'resource' => $model::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        Webhook::create([
            'name' => 'Second Webhook',
            'url' => 'https://second.example.com/webhook',
            'resource' => $model::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        // Act
        $fireWebhook = new FireWebhook;
        $fireWebhook->fireWebhook($model::class, WebhookAction::CREATE->value, $model);

        // Assert
        Bus::assertDispatchedTimes(\Spatie\WebhookServer\CallWebhookJob::class, 2);
    }

    #[Test]
    final public function it_can_handle_webhook_triggered_event(): void
    {
        // Arrange
        Bus::fake();

        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $model::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        $webhookTriggered = new WebhookTriggered($model, WebhookAction::CREATE->value);

        // Act
        $fireWebhook = new FireWebhook;
        $fireWebhook->asListener($webhookTriggered);

        // Assert
        Bus::assertDispatched(\Spatie\WebhookServer\CallWebhookJob::class, function ($job): bool {
            return $job->webhookUrl === 'https://example.com/webhook';
        });
    }
}
