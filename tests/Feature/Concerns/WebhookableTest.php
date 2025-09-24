<?php

namespace Opscale\NovaWebhooks\Tests\Feature\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Opscale\NovaWebhooks\Concerns\Webhookable;
use Opscale\NovaWebhooks\Models\Enums\WebhookAction;
use Opscale\NovaWebhooks\Models\Webhook;
use Opscale\NovaWebhooks\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(Webhookable::class)]
final class WebhookableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Event::fake(); // Temporarily commented out to debug
    }

    #[Test]
    final public function it_registers_webhook_observer_when_trait_is_used(): void
    {
        // Arrange - Use Bus fake to check if webhook action is called
        Bus::fake();

        $modelClass = new class extends Model
        {
            use Webhookable;

            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        // Create a webhook that would be triggered
        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $modelClass::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        // Act - Create model instance which should trigger observer registration
        $model = new $modelClass;
        $model->name = 'Test Model';
        $model->save();

        // Assert - Webhook job should be dispatched if observer works
        Bus::assertDispatched(\Spatie\WebhookServer\CallWebhookJob::class);
    }

    #[Test]
    final public function it_triggers_webhook_events_on_model_creation(): void
    {
        // Arrange
        Bus::fake();

        $modelClass = new class extends Model
        {
            use Webhookable;

            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $modelClass::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        // Act
        $modelClass::create(['name' => 'Created Model']);

        // Assert
        Bus::assertDispatched(\Spatie\WebhookServer\CallWebhookJob::class);
    }

    #[Test]
    final public function it_triggers_webhook_events_on_model_update(): void
    {
        // Arrange
        Bus::fake();

        $modelClass = new class extends Model
        {
            use Webhookable;

            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        $model = $modelClass::create(['name' => 'Original Name']);

        // Clear any previous jobs and create UPDATE webhook
        Bus::fake();

        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $modelClass::class,
            'action' => WebhookAction::UPDATE->value,
            'enabled' => true,
        ]);

        // Act
        $model->update(['name' => 'Updated Name']);

        // Assert
        Bus::assertDispatched(\Spatie\WebhookServer\CallWebhookJob::class);
    }

    #[Test]
    final public function it_triggers_webhook_events_on_model_deletion(): void
    {
        // Arrange
        $modelClass = new class extends Model
        {
            use Webhookable;

            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        $model = $modelClass::create(['name' => 'To Be Deleted']);

        // Clear any previous jobs and create DELETE webhook
        Bus::fake();

        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $modelClass::class,
            'action' => WebhookAction::DELETE->value,
            'enabled' => true,
        ]);

        // Act
        $model->delete();

        // Assert
        Bus::assertDispatched(\Spatie\WebhookServer\CallWebhookJob::class);
    }

    #[Test]
    final public function it_works_with_multiple_models_using_the_trait(): void
    {
        // Arrange
        Bus::fake();

        $firstModelClass = new class extends Model
        {
            use Webhookable;

            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        $secondModelClass = new class extends Model
        {
            use Webhookable;

            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        Webhook::create([
            'name' => 'First Model Webhook',
            'url' => 'https://example.com/webhook1',
            'resource' => $firstModelClass::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        Webhook::create([
            'name' => 'Second Model Webhook',
            'url' => 'https://example.com/webhook2',
            'resource' => $secondModelClass::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        // Act
        $firstModelClass::create(['name' => 'First Model']);
        $secondModelClass::create(['name' => 'Second Model']);

        // Assert
        Bus::assertDispatchedTimes(\Spatie\WebhookServer\CallWebhookJob::class, 2);
    }

    #[Test]
    final public function boot_webhookable_method_is_called_automatically(): void
    {
        // This test verifies that the bootWebhookable method is called when a model uses the trait
        // We can't directly test the boot method call, but we can verify its effect (observer registration)

        // Arrange
        Bus::fake();

        $modelClass = new class extends Model
        {
            use Webhookable;

            protected $table = 'test_models';

            protected $fillable = ['name'];
        };

        Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'resource' => $modelClass::class,
            'action' => WebhookAction::CREATE->value,
            'enabled' => true,
        ]);

        // Act - Perform an action that would trigger observer
        $model = new $modelClass;
        $model->fill(['name' => 'Test'])->save();

        // Assert - Verify that webhook jobs are dispatched (indicating observer is registered)
        Bus::assertDispatched(\Spatie\WebhookServer\CallWebhookJob::class);
    }
}
