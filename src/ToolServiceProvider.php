<?php

namespace Opscale\NovaWebhooks;

use Illuminate\Support\Facades\Event;
use Opscale\NovaPackageTools\NovaPackage;
use Opscale\NovaPackageTools\NovaPackageServiceProvider;
use Opscale\NovaWebhooks\Events\WebhookTriggered;
use Opscale\NovaWebhooks\Nova\Webhook;
use Opscale\NovaWebhooks\Services\Actions\FireWebhook;
use Override;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;

class ToolServiceProvider extends NovaPackageServiceProvider
{
    /**
     * @phpstan-ignore solid.ocp.conditionalOverride
     */
    public function configurePackage(Package $package): void
    {
        /** @var NovaPackage $package */
        $package
            ->name('nova-webhooks')
            ->discoversMigrations()
            ->runsMigrations()
            /** @phpstan-ignore argument.type */
            ->hasResources([
                Webhook::class,
            ])
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->askToStarRepoOnGitHub('opscale-co/nova-webhooks');
            });
    }

    /**
     * Register the event listeners.
     */
    #[Override]
    final public function packageBooted(): void
    {
        parent::packageBooted();

        Event::listen(
            WebhookTriggered::class,
            FireWebhook::class
        );
    }
}
