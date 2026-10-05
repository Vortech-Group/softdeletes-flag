<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Tests\Fixtures;

enum Status: string
{
    case Active = 'active';
    case Archived = 'archived';
}
