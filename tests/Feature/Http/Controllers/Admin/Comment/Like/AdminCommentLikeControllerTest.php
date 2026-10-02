<?php

// tests/Feature/Admin/Comment/Like/AdminCommentLikeControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Admin\Comment\Like;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Enums\Page\DefaultPage;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\CommentLike;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class AdminCommentLikeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::ADMIN);

        $this->comment = Comment::factory()->create();

        // Create required page
        Page::create([
            'slug' => DefaultPage::ADMIN_COMMENT_LIKES->value,
            'is_active' => true,
            'title' => 'Admin Comment Likes',
            'content' => 'Test content',
        ]);

        $this->actingAs($this->admin);
    }

    // ========== FULL PAGE LOAD TESTS ==========

    #[Test]
    public function it_displays_comment_likes_index_page()
    {
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->get(route('admin.comment.like.index', $this->comment->id));

        $response->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('page')
                    ->missing('likes')
                    ->missing('comment')
            );
    }

    #[Test]
    public function it_displays_comment_likes_index_page_with_pagination()
    {
        $users = User::factory()->count(25)->create();
        foreach ($users as $user) {
            CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->get(route('admin.comment.like.index', [
            'comment' => $this->comment->id,
            'per_page' => 10,
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('page')
                    ->missing('likes')
                    ->missing('comment')
            );
    }

    #[Test]
    public function it_displays_create_comment_like_page()
    {
        $response = $this->get(route('admin.comment.like.create', $this->comment->id));

        $response->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('page')
                    ->has('comment')

            );
    }

    #[Test]
    public function it_displays_edit_comment_like_page()
    {
        $user = User::factory()->create();
        $like = CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->get(route('admin.comment.like.edit', [
            'comment' => $this->comment->id,
            'commentLike' => $like->id,
        ]));

        $response->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('page')
                    ->has('comment')
                    ->has('comment_like')
            );
    }

    // ========== CREATE LIKE TESTS ==========

    #[Test]
    public function it_stores_a_new_comment_like()
    {
        $user = User::factory()->create();

        $response = $this->post(route('admin.comment.like.store', $this->comment->id), [
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment like created successfully.');

        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        $this->assertEquals(1, $this->comment->fresh()->likes_count);
    }

    #[Test]
    public function it_validates_user_id_when_creating_like()
    {
        $response = $this->post(route('admin.comment.like.store', $this->comment->id), []);

        $response->assertSessionHasErrors(['user_id']);
    }

    #[Test]
    public function it_validates_user_id_exists_when_creating_like()
    {
        $response = $this->post(route('admin.comment.like.store', $this->comment->id), [
            'user_id' => 99999,
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    // ========== UPDATE LIKE TESTS ==========

    #[Test]
    public function it_updates_a_comment_like()
    {
        $user = User::factory()->create();
        $like = CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
        ]);

        $newUser = User::factory()->create();

        $response = $this->patch(route('admin.comment.like.update', [
            'comment' => $this->comment->id,
            'commentLike' => $like->id,
        ]), [
            'user_id' => $newUser->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment like updated successfully.');

        $this->assertDatabaseHas('comment_likes', [
            'id' => $like->id,
            'user_id' => $newUser->id,
        ]);
    }

    #[Test]
    public function it_updates_like_ip_address()
    {
        $user = User::factory()->create();
        $like = CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->patch(route('admin.comment.like.update', [
            'comment' => $this->comment->id,
            'commentLike' => $like->id,
        ]), [
            'ip_address' => '192.168.1.100',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment like updated successfully.');

        $this->assertDatabaseHas('comment_likes', [
            'id' => $like->id,
            'ip_address' => '192.168.1.100',
        ]);
    }

    #[Test]
    public function it_validates_user_id_when_updating_like()
    {
        $user = User::factory()->create();
        $like = CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->patch(route('admin.comment.like.update', [
            'comment' => $this->comment->id,
            'commentLike' => $like->id,
        ]), [
            'user_id' => '',
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    #[Test]
    public function it_cannot_update_like_that_does_not_belong_to_comment()
    {
        $otherComment = Comment::factory()->create();
        $user = User::factory()->create();

        $like = CommentLike::create([
            'comment_id' => $otherComment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->patch(route('admin.comment.like.update', [
            'comment' => $this->comment->id,
            'commentLike' => $like->id,
        ]), [
            'ip_address' => '192.168.1.100',
        ]);

        $response->assertSessionHas('error', 'This like does not belong to this comment.');
    }

    #[Test]
    public function it_deletes_a_comment_like()
    {
        $user = User::factory()->create();
        $like = CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->delete(route('admin.comment.like.destroy', [
            'comment' => $this->comment->id,
            'commentLike' => $like->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment like deleted successfully.');

        $this->assertDatabaseMissing('comment_likes', ['id' => $like->id]);
        $this->assertEquals(0, $this->comment->fresh()->likes_count);
    }

    #[Test]
    public function it_cannot_delete_like_that_does_not_belong_to_comment()
    {
        $otherComment = Comment::factory()->create();
        $user = User::factory()->create();

        $like = CommentLike::create([
            'comment_id' => $otherComment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->delete(route('admin.comment.like.destroy', [
            'comment' => $this->comment->id,
            'commentLike' => $like->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This like does not belong to this comment.');
    }

    #[Test]
    public function it_bulk_deletes_comment_likes()
    {
        $users = User::factory()->count(3)->create();
        $likes = [];

        foreach ($users as $user) {
            $likes[] = CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $likeIds = collect($likes)->pluck('id')->toArray();

        $response = $this->post(route('admin.comment.like.bulk.destroy', $this->comment->id), [
            'ids' => $likeIds,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Selected comment likes deleted successfully.');

        foreach ($likes as $like) {
            $this->assertDatabaseMissing('comment_likes', ['id' => $like->id]);
        }

        $this->assertEquals(0, $this->comment->fresh()->likes_count);
    }

    #[Test]
    public function it_validates_bulk_delete_likes_request()
    {
        $response = $this->post(route('admin.comment.like.bulk.destroy', $this->comment->id), [
            'ids' => ['invalid'],
        ]);

        $response->assertSessionHasErrors(['ids.0']);
    }

    // ========== PARTIAL RELOAD TESTS (JSON Assertions) ==========

    #[Test]
    public function it_doesnt_return_all_props_for_inertia_request_without_partial_headers()
    {
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->get(route('admin.comment.like.index', $this->comment->id));

        $response->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('page')
                    ->missing('likes')
                    ->missing('comment')
            );
    }

    #[Test]
    public function it_returns_only_requested_props_for_partial_reload()
    {
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/like/index',
            'X-Inertia-Partial-Data' => 'likes',
        ])->get(route('admin.comment.like.index', $this->comment->id));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        $this->assertEquals('admin/comment/like/index', $data['component']);

        $this->assertArrayHasKey('likes', $data['props']);
        $this->assertCount(3, $data['props']['likes']['data']);
        $this->assertArrayNotHasKey('comment', $data['props']);
        $this->assertArrayNotHasKey('page', $data['props']);
    }

    #[Test]
    public function it_returns_multiple_requested_props_for_partial_reload()
    {
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/like/index',
            'X-Inertia-Partial-Data' => 'likes,comment',
        ])->get(route('admin.comment.like.index', $this->comment->id));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        $this->assertEquals('admin/comment/like/index', $data['component']);

        $this->assertArrayHasKey('likes', $data['props']);
        $this->assertArrayHasKey('comment', $data['props']);
        $this->assertArrayNotHasKey('page', $data['props']);
    }

    #[Test]
    public function it_returns_paginated_likes_for_partial_reload()
    {
        $users = User::factory()->count(25)->create();
        foreach ($users as $user) {
            CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/like/index',
            'X-Inertia-Partial-Data' => 'likes',
        ])->get(route('admin.comment.like.index', [
            'comment' => $this->comment->id,
            'per_page' => 10,
            'page' => 2,
        ]));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        $this->assertArrayHasKey('likes', $data['props']);
        $this->assertCount(10, $data['props']['likes']['data']);
        $this->assertEquals(2, $data['props']['likes']['meta']['current_page']);
        $this->assertEquals(10, $data['props']['likes']['meta']['per_page']);
        $this->assertEquals(25, $data['props']['likes']['meta']['total']);
        $this->assertEquals(3, $data['props']['likes']['meta']['last_page']);
    }

    #[Test]
    public function it_returns_empty_collection_for_partial_reload_when_no_likes()
    {
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/like/index',
            'X-Inertia-Partial-Data' => 'likes',
        ])->get(route('admin.comment.like.index', $this->comment->id));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        $this->assertArrayHasKey('likes', $data['props']);
        $this->assertCount(0, $data['props']['likes']['data']);
        $this->assertEquals(0, $data['props']['likes']['meta']['total']);

        $this->assertArrayNotHasKey('comment', $data['props']);
        $this->assertArrayNotHasKey('page', $data['props']);
    }

    // ========== ERROR HANDLING TESTS ==========

    #[Test]
    public function it_returns_404_when_page_not_found()
    {
        Page::where('slug', DefaultPage::ADMIN_COMMENT_LIKES->value)->delete();

        $response = $this->get(route('admin.comment.like.index', $this->comment->id));

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_comment_not_found()
    {
        $response = $this->get(route('admin.comment.like.index', 99999));

        $response->assertStatus(404);
    }
}
