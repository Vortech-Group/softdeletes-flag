<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vortech\SoftDeletesFlag\Traits\SoftDeletesFlag;

class Comment extends Model
{
    use SoftDeletesFlag;

    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
