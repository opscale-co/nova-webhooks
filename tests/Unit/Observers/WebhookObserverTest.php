<?php

namespace Opscale\NovaWebhooks\Tests\Unit\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Opscale\NovaWebhooks\Events\WebhookTriggered;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Opscale\NovaWebhooks\Observers\WebhookObserver;
use Opscale\NovaWebhooks\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(WebhookObserver::class)]
final class WebhookObserverTest extends TestCase
{
    private WebhookObserver $webhookObserver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookObserver = new WebhookObserver;
        Event::fake();
    }

    #[Test]
    final public function it_dispatches_webhook_triggered_event_when_model_is_created(): void
    {
        // Arrange
        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };
        $model->name = 'Test Model';

        // Act
        $this->webhookObserver->created($model);

        // Assert
        Event::assertDispatched(WebhookTriggered::class, function ($event) use ($model): bool {
            return $event->model === $model
                && $event->action === WebhookAction::CREATE->value;
        });
    }

    #[Test]
    final public function it_dispatches_webhook_triggered_event_when_model_is_updated(): void
    {
        // Arrange
        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };
        $model->name = 'Updated Model';

        // Act
        $this->webhookObserver->updated($model);

        // Assert
        Event::assertDispatched(WebhookTriggered::class, function ($event) use ($model): bool {
            return $event->model === $model
                && $event->action === WebhookAction::UPDATE->value;
        });
    }

    #[Test]
    final public function it_dispatches_webhook_triggered_event_when_model_is_deleted(): void
    {
        // Arrange
        $model = new class extends Model
        {
            protected $table = 'test_models';

            protected $fillable = ['name'];
        };
        $model->name = 'Deleted Model';

        // Act
        $this->webhookObserver->deleted($model);

        // Assert
        Event::assertDispatched(WebhookTriggered::class, function ($event) use ($model): bool {
            return $event->model === $model
                && $event->action === WebhookAction::DELETE->value;
        });
    }

    #[Test]
    final public function it_only_dispatches_one_event_per_observer_method(): void
    {
        // Arrange
        $model = new class extends Model
        {
            protected $table = 'test_models';
        };

        // Act
        $this->webhookObserver->created($model);
        $this->webhookObserver->updated($model);
        $this->webhookObserver->deleted($model);

        // Assert
        Event::assertDispatchedTimes(WebhookTriggered::class, 3);
    }

    #[Test]
    final public function it_handles_different_model_types(): void
    {
        // Arrange
        $firstModel = new class extends Model
        {
            protected $table = 'first_models';
        };

        $secondModel = new class extends Model
        {
            protected $table = 'second_models';
        };

        // Act
        $this->webhookObserver->created($firstModel);
        $this->webhookObserver->updated($secondModel);

        // Assert
        Event::assertDispatched(WebhookTriggered::class, function ($event) use ($firstModel): bool {
            return $event->model === $firstModel
                && $event->action === WebhookAction::CREATE->value;
        });

        Event::assertDispatched(WebhookTriggered::class, function ($event) use ($secondModel): bool {
            return $event->model === $secondModel
                && $event->action === WebhookAction::UPDATE->value;
        });
    }
}
