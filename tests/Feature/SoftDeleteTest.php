<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Vortech\SoftDeletesFlag\Tests\Fixtures\Comment;
use Vortech\SoftDeletesFlag\Tests\Fixtures\Post;

it('flags the row instead of removing it on delete', function () {
    $post = Post::create(['title' => 'a']);

    expect($post->delete())->toBeTrue()
        ->and($post->trashed())->toBeTrue()
        ->and($post->exists)->toBeTrue();

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_deleted' => true]);
});

it('casts the is_deleted column to boolean', function () {
    $post = Post::create(['title' => 'a'])->fresh();

    expect($post->is_deleted)->toBeFalse()
        ->and($post->trashed())->toBeFalse();
});

it('excludes trashed models by default', function () {
    Post::create(['title' => 'a']);
    Post::create(['title' => 'b'])->delete();

    expect(Post::pluck('title')->all())->toBe(['a'])
        ->and(Post::where('title', 'b')->first())->toBeNull();
});

it('includes trashed models with withTrashed', function () {
    Post::create(['title' => 'a']);
    Post::create(['title' => 'b'])->delete();

    expect(Post::withTrashed()->count())->toBe(2)
        ->and(Post::withTrashed(false)->count())->toBe(1);
});

it('returns only trashed models with onlyTrashed', function () {
    Post::create(['title' => 'a']);
    Post::create(['title' => 'b'])->delete();

    expect(Post::onlyTrashed()->pluck('title')->all())->toBe(['b']);
});

it('excludes trashed models with withoutTrashed', function () {
    Post::create(['title' => 'a']);
    Post::create(['title' => 'b'])->delete();

    expect(Post::withTrashed()->withoutTrashed()->pluck('title')->all())->toBe(['a']);
});

it('restores a trashed model', function () {
    $post = Post::create(['title' => 'a']);
    $post->delete();

    $post->restore();

    expect($post->trashed())->toBeFalse()
        ->and(Post::count())->toBe(1);
});

it('soft deletes through the query builder', function () {
    Post::create(['title' => 'a']);
    Post::create(['title' => 'b']);

    expect(Post::query()->delete())->toBe(2)
        ->and(Post::count())->toBe(0)
        ->and(Post::onlyTrashed()->count())->toBe(2);
});

it('restores through the query builder', function () {
    Post::create(['title' => 'a'])->delete();
    Post::create(['title' => 'b'])->delete();

    expect(Post::onlyTrashed()->restore())->toBe(2)
        ->and(Post::count())->toBe(2);
});

it('removes the row on force delete', function () {
    $post = Post::create(['title' => 'a']);

    expect($post->forceDelete())->toBeTrue()
        ->and($post->exists)->toBeFalse();

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('force deletes a trashed model', function () {
    $post = Post::create(['title' => 'a']);
    $post->delete();

    $post->forceDelete();

    expect(Post::withTrashed()->count())->toBe(0);
});

it('force deletes through the query builder', function () {
    Post::create(['title' => 'a']);
    Post::create(['title' => 'b']);

    Post::query()->forceDelete();

    expect(Post::withTrashed()->count())->toBe(0);
});

it('resets the force deleting state after force delete', function () {
    $post = Post::create(['title' => 'a']);

    $post->forceDelete();

    expect($post->isForceDeleting())->toBeFalse();
});

it('restores a trashed model with restoreOrCreate', function () {
    $post = Post::create(['title' => 'a']);
    $post->delete();

    $restored = Post::restoreOrCreate(['title' => 'a']);

    expect($restored->is($post))->toBeTrue()
        ->and($restored->trashed())->toBeFalse()
        ->and(Post::withTrashed()->count())->toBe(1);
});

it('creates a missing model with restoreOrCreate', function () {
    $post = Post::restoreOrCreate(['title' => 'new']);

    expect($post->exists)->toBeTrue()
        ->and(Post::count())->toBe(1);
});

it('restores a trashed model with createOrRestore', function () {
    $post = Post::create(['title' => 'a']);
    $post->delete();

    $restored = Post::createOrRestore(['title' => 'a']);

    expect($restored->is($post))->toBeTrue()
        ->and($restored->trashed())->toBeFalse()
        ->and(Post::withTrashed()->count())->toBe(1);
});

it('updates the updated_at timestamp on delete', function () {
    $post = Post::create(['title' => 'a']);
    $this->travel(5)->minutes();

    $post->delete();

    expect($post->updated_at->gt($post->created_at))->toBeTrue();
});

it('fires the model events', function () {
    $fired = [];
    foreach (['trashed', 'restoring', 'restored', 'forceDeleting', 'forceDeleted'] as $event) {
        Post::registerModelEvent($event, function () use (&$fired, $event) {
            $fired[] = $event;
        });
    }

    $post = Post::create(['title' => 'a']);
    $post->delete();
    $post->restore();
    $post->forceDelete();

    expect($fired)->toBe(['trashed', 'restoring', 'restored', 'forceDeleting', 'forceDeleted']);
});

it('registers events through the helper methods', function () {
    $fired = [];
    Post::softDeleted(function () use (&$fired) {
        $fired[] = 'trashed';
    });
    Post::restoring(function () use (&$fired) {
        $fired[] = 'restoring';
    });
    Post::restored(function () use (&$fired) {
        $fired[] = 'restored';
    });
    Post::forceDeleting(function () use (&$fired) {
        $fired[] = 'forceDeleting';
    });
    Post::forceDeleted(function () use (&$fired) {
        $fired[] = 'forceDeleted';
    });

    $post = Post::create(['title' => 'a']);
    $post->delete();
    $post->restore();
    $post->forceDelete();

    expect($fired)->toBe(['trashed', 'restoring', 'restored', 'forceDeleting', 'forceDeleted']);
});

it('can cancel a restore from the restoring event', function () {
    Post::restoring(fn () => false);

    $post = Post::create(['title' => 'a']);
    $post->delete();

    expect($post->restore())->toBeFalse()
        ->and(Post::onlyTrashed()->count())->toBe(1);
});

it('can cancel a force delete from the forceDeleting event', function () {
    Post::forceDeleting(fn () => false);

    $post = Post::create(['title' => 'a']);

    expect($post->forceDelete())->toBeFalse()
        ->and(Post::count())->toBe(1);
});

it('does not fire events from the quiet variants', function () {
    Event::fake();

    $post = Post::create(['title' => 'a']);
    $post->delete();
    $post->restoreQuietly();
    $post->forceDeleteQuietly();

    Event::assertNotDispatched('eloquent.restoring: ' . Post::class);
    Event::assertNotDispatched('eloquent.restored: ' . Post::class);
    Event::assertNotDispatched('eloquent.forceDeleting: ' . Post::class);
    Event::assertNotDispatched('eloquent.forceDeleted: ' . Post::class);
});

it('respects a custom column name', function () {
    config(['softdeletes-flag.column_name' => 'removed']);

    $post = new Post();

    expect($post->getIsDeletedColumn())->toBe('removed')
        ->and($post->getQualifiedIsDeletedColumn())->toBe('posts.removed');
});

it('excludes trashed models from relations', function () {
    $post = Post::create(['title' => 'a']);
    $post->comments()->create(['body' => 'x']);
    $post->comments()->create(['body' => 'y'])->delete();

    expect($post->comments()->pluck('body')->all())->toBe(['x'])
        ->and($post->comments()->withTrashed()->count())->toBe(2);
});

it('qualifies the column when deleting with a join', function () {
    $post = Post::create(['title' => 'a']);
    $post->comments()->create(['body' => 'x']);

    Post::query()->join('comments', 'comments.post_id', '=', 'posts.id')->delete();

    expect(Post::onlyTrashed()->count())->toBe(1)
        ->and(Comment::count())->toBe(1);
});
