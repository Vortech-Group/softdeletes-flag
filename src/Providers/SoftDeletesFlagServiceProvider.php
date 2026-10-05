<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Vortech\SoftDeletesFlag\Console\InstallCommand;

final class SoftDeletesFlagServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->offerPublishing();

        $this->registerMacroHelpers();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../../config/softdeletes-flag.php',
            key: 'softdeletes-flag'
        );

        AliasLoader::getInstance()
            ->alias(
                alias: 'Illuminate\Routing\ImplicitRouteBinding',
                class: 'Vortech\SoftDeletesFlag\Routing\ImplicitRouteBinding'
            );
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([InstallCommand::class]);

        $this->publishes(
            paths: [
                __DIR__.'/../../config/softdeletes-flag.php' => config_path('softdeletes-flag.php'),
            ],
            groups: 'softdeletes-config'
        );
    }

    protected function registerMacroHelpers(): void
    {
        Blueprint::macro('softDeletesFlag', function (): void {
            $this->boolean(config('softdeletes-flag.column_name'))
                ->default(false)
                ->index();
        });

        Blueprint::macro('dropSoftDeletesFlag', function (): void {
            $column = config('softdeletes-flag.column_name');

            $this->dropIndex([$column]);
            $this->dropColumn($column);
        });
    }
}
