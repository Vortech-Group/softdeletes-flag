<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('adds the column with softDeletesFlag', function () {
    expect(Schema::hasColumn('posts', 'is_deleted'))->toBeTrue();
});

it('indexes the column', function () {
    $indexes = collect(Schema::getIndexes('posts'))->pluck('columns')->all();

    expect($indexes)->toContain(['is_deleted']);
});

it('defaults the column to false', function () {
    $id = DB::table('posts')->insertGetId(['title' => 'a']);

    expect((int) DB::table('posts')->where('id', $id)->value('is_deleted'))->toBe(0);
});

it('takes the column name from the config', function () {
    config(['softdeletes-flag.column_name' => 'removed']);

    Schema::create('others', fn (Blueprint $table) => $table->softDeletesFlag());

    expect(Schema::hasColumn('others', 'removed'))->toBeTrue();
});

it('drops the column with dropSoftDeletesFlag', function () {
    Schema::table('comments', function (Blueprint $table) {
        $table->dropSoftDeletesFlag();
    });

    expect(Schema::hasColumn('comments', 'is_deleted'))->toBeFalse();
});
