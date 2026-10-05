<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vortech\SoftDeletesFlag\Traits\SoftDeletesFlag;

class Post extends Model
{
    use SoftDeletesFlag;

    protected $guarded = [];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
