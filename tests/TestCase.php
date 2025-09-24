<?php

namespace Opscale\NovaWebhooks\Tests;

use Illuminate\Support\Facades\Route;
use Opscale\NovaWebhooks\ToolServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middlewareGroup('nova', []);
        $this->withoutExceptionHandling();

        // Create necessary tables for testing
        $this->setUpDatabase();
    }

    /**
     * Define environment setup.
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup queue to use sync driver for testing
        $app['config']->set('queue.default', 'sync');

        // Setup webhook-server config
        $app['config']->set('webhook-server', [
            'webhook_job' => \Spatie\WebhookServer\CallWebhookJob::class,
            'queue' => null,
            'connection' => null,
            'http_verb' => 'post',
            'tries' => 3,
            'timeout_in_seconds' => 10,
            'backoff_strategy' => \Spatie\WebhookServer\BackoffStrategy\ExponentialBackoffStrategy::class,
            'base_backoff_time_in_seconds' => 1,
            'max_backoff_time_in_seconds' => 10,
            'sign_calls' => false,
            'signature_header_name' => 'Signature',
            'signature_validator' => \Spatie\WebhookServer\Signer\DefaultSigner::class,
            'signer' => \Spatie\WebhookServer\Signer\DefaultSigner::class,
            'webhook_signing_secret' => '',
            'headers' => [],
            'filter' => null,
            'verify_ssl' => true,
            'tags' => [],
            'throw_exception_on_failure' => false,
            'proxy' => null,
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ToolServiceProvider::class,
        ];
    }

    private function setUpDatabase(): void
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        // Create webhooks table
        $schema->create('webhooks', function ($table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url');
            $table->json('headers')->nullable();
            $table->string('resource');
            $table->string('action');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // Create test_models table
        $schema->create('test_models', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
}
