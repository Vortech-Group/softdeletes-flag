<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Facades;

use Illuminate\Support\Facades\Facade;

final class SoftDeletesFlagFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'softdeletes-flag';
    }
}
