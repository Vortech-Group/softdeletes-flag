<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Illuminate\Routing\Exceptions;

use RuntimeException;

final class BackedEnumCaseNotFoundException extends RuntimeException
{
    public function __construct($backedEnumClass, $case)
    {
        parent::__construct("Case [{$case}] not found on Backed Enum [{$backedEnumClass}].");
    }
}
