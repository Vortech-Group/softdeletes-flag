<?php

namespace Vortech\SoftDeletesFlag\Facades;

use Illuminate\Support\Facades\Facade;

class SoftDeletesFlagFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'softdeletes-flag';
    }
}
