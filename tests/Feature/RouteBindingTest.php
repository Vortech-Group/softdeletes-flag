<?php

declare(strict_types=1);

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Vortech\SoftDeletesFlag\Tests\Fixtures\Comment;
use Vortech\SoftDeletesFlag\Tests\Fixtures\Post;
use Vortech\SoftDeletesFlag\Tests\Fixtures\Status;
use Vortech\SoftDeletesFlag\Tests\Fixtures\Tag;

beforeEach(function () {
    Route::middleware(SubstituteBindings::class)->group(function () {
        Route::get('/posts/{post}', fn (Post $post) => $post->title);

        Route::get('/trashed/{post}', fn (Post $post) => $post->title)->withTrashed();

        Route::get('/status/{status}', fn (Status $status) => $status->value);

        Route::get('/posts/{post}/comments/{comment}', fn (Post $post, Comment $comment) => $comment->body)
            ->scopeBindings();

        Route::get('/trashed/{post}/comments/{comment}', fn (Post $post, Comment $comment) => $comment->body)
            ->scopeBindings()
            ->withTrashed();

        Route::get('/tags/{tag}', fn (Tag $tag) => $tag->name);

        Route::get('/trashed-tags/{tag}', fn (Tag $tag) => $tag->name)->withTrashed();

        Route::get('/orphan/{foo}', fn (Post $post) => 'ok');

        Route::get('/by-title/{post:title}', fn (Post $post) => (string) $post->id);
    });
});

it('resolves an existing model', function () {
    $post = Post::create(['title' => 'hello']);

    $this->get("/posts/{$post->id}")->assertOk()->assertSee('hello');
});

it('returns 404 for a trashed model', function () {
    $post = Post::create(['title' => 'hello']);
    $post->delete();

    $this->get("/posts/{$post->id}")->assertNotFound();
});

it('returns 404 for a missing model', function () {
    $this->get('/posts/999')->assertNotFound();
});

it('resolves a trashed model with withTrashed', function () {
    $post = Post::create(['title' => 'hello']);
    $post->delete();

    $this->get("/trashed/{$post->id}")->assertOk()->assertSee('hello');
});

it('supports a custom binding field', function () {
    $post = Post::create(['title' => 'hello']);

    $this->get('/by-title/hello')->assertOk()->assertSee((string) $post->id);

    $post->delete();

    $this->get('/by-title/hello')->assertNotFound();
});

it('resolves backed enums', function () {
    $this->get('/status/active')->assertOk()->assertSee('active');
});

it('returns 404 for an invalid backed enum case', function () {
    $this->get('/status/nope')->assertNotFound();
});

it('resolves a scoped child of the parent', function () {
    $post = Post::create(['title' => 'hello']);
    $comment = $post->comments()->create(['body' => 'nice']);

    $this->get("/posts/{$post->id}/comments/{$comment->id}")->assertOk()->assertSee('nice');
});

it('rejects a scoped child that belongs to another parent', function () {
    $post = Post::create(['title' => 'hello']);
    $other = Post::create(['title' => 'other']);
    $comment = $other->comments()->create(['body' => 'nice']);

    $this->get("/posts/{$post->id}/comments/{$comment->id}")->assertNotFound();
});

it('rejects a trashed scoped child', function () {
    $post = Post::create(['title' => 'hello']);
    $comment = $post->comments()->create(['body' => 'nice']);
    $comment->delete();

    $this->get("/posts/{$post->id}/comments/{$comment->id}")->assertNotFound();
});

it('resolves a trashed scoped child with withTrashed', function () {
    $post = Post::create(['title' => 'hello']);
    $comment = $post->comments()->create(['body' => 'nice']);
    $comment->delete();

    $this->get("/trashed/{$post->id}/comments/{$comment->id}")->assertOk()->assertSee('nice');
});

it('keeps the native SoftDeletes behaviour for other models', function () {
    $tag = Tag::create(['name' => 'php']);
    $tag->delete();

    $this->get("/tags/{$tag->id}")->assertNotFound();
    $this->get("/trashed-tags/{$tag->id}")->assertOk()->assertSee('php');
});

it('ignores signature parameters that are not route parameters', function () {
    $this->get('/orphan/1')->assertOk()->assertSee('ok');
});
