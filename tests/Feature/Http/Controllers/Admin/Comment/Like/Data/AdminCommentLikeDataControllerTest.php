<?php

// tests/Feature/Admin/Comment/Like/Data/AdminCommentLikeDataControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Admin\Comment\Like\Data;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\CommentLike;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class AdminCommentLikeDataControllerTest extends TestCase
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
        $this->actingAs($this->admin);
    }

    #[Test]
    public function it_can_get_paginated_comment_likes_data()
    {
        // Create 25 likes using User factory
        $users = User::factory()->count(25)->create();
        foreach ($users as $user) {
            CommentLike::create([
                'comment_id' => $this->comment->id,
                'user_id' => $user->getId(),
                'ip_address' => '127.0.0.1',
            ]);
        }

        $response = $this->get(route('admin.comment.like.data.index', [
            'comment' => $this->comment->id,
            'per_page' => 10,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'user_id', 'comment_id', 'created_at'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'links',
        ]);

        $data = $response->json();
        $this->assertCount(10, $data['data']);
        $this->assertEquals(25, $data['meta']['total']);
    }

    #[Test]
    public function it_can_filter_comment_likes_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user1->id,
            'ip_address' => '127.0.0.1',
        ]);

        CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user2->id,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->get(route('admin.comment.like.data.index', [
            'comment' => $this->comment->id,
            'user_id' => $user1->id,
        ]));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($user1->id, $data[0]['user_id']);
    }

    #[Test]
    public function it_can_filter_comment_likes_by_ip_address()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user1->id,
            'ip_address' => '192.168.1.1',
        ]);

        CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user2->id,
            'ip_address' => '192.168.1.2',
        ]);

        $response = $this->get(route('admin.comment.like.data.index', [
            'comment' => $this->comment->id,
            'ip_address' => '192.168.1.1',
        ]));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('192.168.1.1', $data[0]['ip_address']);
    }

    #[Test]
    public function it_can_sort_comment_likes_by_created_at()
    {
        $user = User::factory()->create();

        $like1 = CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(2),
        ]);

        $like2 = CommentLike::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'ip_address' => '127.0.0.2',
            'created_at' => now(),
        ]);

        $response = $this->get(route('admin.comment.like.data.index', [
            'comment' => $this->comment->id,
            'sort' => 'created_at',
            'order' => 'desc',
        ]));

        $data = $response->json('data');

        $this->assertEquals($like2->id, $data[0]['id']);
        $this->assertEquals($like1->id, $data[1]['id']);
    }
}
