<?php

namespace Vortech\SoftDeletesFlag\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class SoftDeletesFlagServiceProvider extends ServiceProvider
{
    public function boot(): void {}

    public function register(): void
    {
        AliasLoader::getInstance()
            ->alias(
                alias: 'Illuminate\Routing\ImplicitRouteBinding',
                class: 'Vortech\SoftDeletesFlag\Illuminate\Routing\ImplicitRouteBinding'
            );

        Blueprint::macro('softDeletesFlag', function () {
            $this->boolean('is_deleted')->default(false)->index();
        });

        Blueprint::macro('dropSoftDeletesFlag', function () {
            $this->dropColumn('is_deleted');
        });
    }
}
