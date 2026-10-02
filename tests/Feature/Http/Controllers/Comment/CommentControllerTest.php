<?php

// tests/Feature/Comment/CommentControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Comment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Enums\Comment\CommentStatus;
use Truvoicer\TfPerspectives\Events\Comment\CommentCreated;
use Truvoicer\TfPerspectives\Events\Comment\CommentDeleted;
use Truvoicer\TfPerspectives\Events\Comment\CommentLiked;
use Truvoicer\TfPerspectives\Events\Comment\CommentUpdated;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\CommentLike;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_create_comment()
    {
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('comment.store'), [
            'service' => 'blog',
            'provider' => 'wordpress',
            'item_id' => 'post_123',
            'content' => 'This is a test comment',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'content' => 'This is a test comment',
            'user_id' => $user->getId(),
        ]);

        Event::assertDispatched(CommentCreated::class);
    }

    #[Test]
    public function guest_can_create_comment_with_ip_tracking()
    {
        Event::fake();

        $response = $this->post(route('comment.store'), [
            'service' => 'blog',
            'provider' => 'wordpress',
            'item_id' => 'post_123',
            'content' => 'Guest comment',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'content' => 'Guest comment',
            'user_id' => null,
            'ip_address' => request()->ip(),
        ]);

        Event::assertDispatched(CommentCreated::class);
    }

    #[Test]
    public function comment_validation_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('comment.store'), [
            'service' => '',
            'provider' => '',
            'item_id' => '',
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['service', 'provider', 'item_id', 'content']);
    }

    #[Test]
    public function user_can_update_their_own_comment()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($user);

        $response = $this->patch(route('comment.update', $comment->id), [
            'content' => 'Updated content',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    // #[Test]
    // public function user_cannot_update_comment_after_edit_window()
    // {
    //     $user = User::factory()->create();
    //     $comment = Comment::factory()->create([
    //         'user_id' => $user->getId(),
    //         'created_at' => now()->subHours(2),
    //     ]);

    //     $this->actingAs($user);

    //     $response = $this->patch(route('comment.update', $comment->id), [
    //         'content' => 'Updated content',
    //     ]);

    //     $response->assertSessionHas('error', 'You cannot edit this comment.');
    // }

    #[Test]
    public function admin_can_update_any_comment()
    {
        Event::fake();

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Admin created content',
        ]);

        $this->actingAs($admin);

        $response = $this->patch(route('comment.update', $comment->id), [
            'content' => 'Admin updated content',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Admin updated content',
        ]);
    }

    #[Test]
    public function user_can_delete_their_own_comment()
    {
        Event::fake();
        $now = now();
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('comment.destroy', $comment->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function user_can_like_comment()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('comment.like', $comment->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->getId(),
        ]);
        $this->assertEquals(1, $comment->fresh()->likes_count);

        Event::assertDispatched(CommentLiked::class);
    }

    #[Test]
    public function user_can_unlike_comment()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        // Like first
        CommentLike::create([
            'comment_id' => $comment->id,
            'user_id' => $user->getId(),
        ]);
        $comment->increment('likes_count');

        $this->actingAs($user);

        $response = $this->post(route('comment.like', $comment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->getId(),
        ]);
        $this->assertEquals(0, $comment->fresh()->likes_count);
    }

    #[Test]
    public function user_can_report_comment()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('comment.report', $comment->id), [
            'reason' => 'spam',
            'details' => 'This is spam content',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comment_reports', [
            'comment_id' => $comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
        ]);
    }

    #[Test]
    public function guest_can_report_comment_with_name_and_email()
    {
        $comment = Comment::factory()->create();

        $response = $this->post(route('comment.report', $comment->id), [
            'reason' => 'spam',
            'details' => 'This is spam',
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comment_reports', [
            'comment_id' => $comment->id,
            'user_id' => null,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'reason' => 'spam',
        ]);
    }

    #[Test]
    public function it_returns_paginated_comments()
    {
        $user = User::factory()->create();
        Comment::factory()->count(25)->create([
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
        ]);

        $response = $this->get(route('comment.data.index', [
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'per_page' => 10,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'links',
        ]);

        $data = $response->json();
        $this->assertCount(10, $data['data']);
        $this->assertEquals(25, $data['meta']['total']);
    }

    #[Test]
    public function it_sorts_comments_correctly()
    {
        $user = User::factory()->create();

        $oldest = Comment::factory()->create([
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
            'likes_count' => 1,
            'created_at' => now()->subDays(5),
        ]);

        $popular = Comment::factory()->create([
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
            'likes_count' => 10,
            'created_at' => now()->subDays(4),
        ]);

        $newest = Comment::factory()->create([
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
            'likes_count' => 3,
            'created_at' => now()->subDays(3),
        ]);

        // Test newest first (default)
        $response = $this->get(route('comment.data.index', [
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'sort' => 'newest',
        ]));

        $data = $response->json('data');

        $this->assertEquals($newest->id, $data[0]['id']);

        // Test oldest first
        $response = $this->get(route('comment.data.index', [
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'sort' => 'oldest',
        ]));

        $data = $response->json('data');

        $this->assertEquals($oldest->id, $data[0]['id']);

        // Test popular
        $response = $this->get(route('comment.data.index', [
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'sort' => 'popular',
        ]));

        $data = $response->json('data');
        $this->assertEquals($popular->id, $data[0]['id']);
    }
}
