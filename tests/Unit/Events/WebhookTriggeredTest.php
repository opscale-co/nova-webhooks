<?php

namespace Opscale\NovaWebhooks\Tests\Unit\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Opscale\NovaWebhooks\Events\WebhookTriggered;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Opscale\NovaWebhooks\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

#[CoversClass(WebhookTriggered::class)]
final class WebhookTriggeredTest extends TestCase
{
    #[Test]
    final public function it_can_be_created_with_model_and_action(): void
    {
        // Arrange
        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };
        $model->name = 'Test Model';

        $action = WebhookAction::CREATE->value;

        // Act
        $webhookTriggered = new WebhookTriggered($model, $action);

        // Assert
        $this->assertSame($model, $webhookTriggered->model);
        $this->assertSame($action, $webhookTriggered->action);
    }

    #[Test]
    final public function it_is_readonly(): void
    {
        // Arrange
        $model = new class extends Model
        {
            protected $table = 'test_models';
        };

        $action = WebhookAction::UPDATE->value;

        // Act
        $webhookTriggered = new WebhookTriggered($model, $action);

        // Assert - Properties should be readonly
        $this->assertInstanceOf(WebhookTriggered::class, $webhookTriggered);

        // Verify readonly nature by checking reflection
        $reflectionClass = new ReflectionClass($webhookTriggered);
        $this->assertTrue($reflectionClass->isReadOnly());
    }

    #[Test]
    final public function it_can_be_dispatched(): void
    {
        // Arrange
        Event::fake();

        $model = new class extends Model
        {
            protected $table = 'test_models';
        };

        $action = WebhookAction::DELETE->value;

        // Act
        WebhookTriggered::dispatch($model, $action);

        // Assert
        Event::assertDispatched(WebhookTriggered::class, function ($event) use ($model, $action): bool {
            return $event->model === $model && $event->action === $action;
        });
    }

    #[Test]
    final public function it_works_with_different_webhook_actions(): void
    {
        // Arrange
        $model = new class extends Model
        {
            protected $table = 'test_models';
        };

        $actions = [
            WebhookAction::CREATE->value,
            WebhookAction::UPDATE->value,
            WebhookAction::DELETE->value,
        ];

        // Act & Assert
        foreach ($actions as $action) {
            $event = new WebhookTriggered($model, $action);

            $this->assertSame($model, $event->model);
            $this->assertSame($action, $event->action);
        }
    }
}
