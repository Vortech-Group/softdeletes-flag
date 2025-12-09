<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

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
            path: __DIR__ . '/../config/softdeletes-flag.php',
            key: 'softdeletes-flag'
        );

        AliasLoader::getInstance()
            ->alias(
                alias: 'Illuminate\Routing\ImplicitRouteBinding',
                class: 'Vortech\SoftDeletesFlag\Illuminate\Routing\ImplicitRouteBinding'
            );
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/softdeletes-flag.php' => config_path('softdeletes-flag.php'),
        ], 'softdeletes-config');
    }

    protected function registerMacroHelpers(): void
    {
        Blueprint::macro('softDeletesFlag', function () {
            $this->boolean(config('softdeletes-flag.column_name'))
                ->default(false)
                ->index();
        });

        Blueprint::macro('dropSoftDeletesFlag', function () {
            $this->dropColumn(config('softdeletes-flag.column_name'));
        });
    }
}
