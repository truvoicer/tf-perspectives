<?php

// tests/Feature/Admin/Comment/Data/AdminCommentDataControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Admin\Comment\Data;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class AdminCommentDataControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::ADMIN);
        $this->actingAs($this->admin);
    }

    #[Test]
    public function it_can_get_paginated_comments_data()
    {
        Comment::factory()->count(25)->create();

        $response = $this->get(route('admin.comment.data.index', [
            'per_page' => 10,
            'page' => 1,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'content', 'user_id', 'created_at'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'links',
        ]);

        $data = $response->json();
        $this->assertCount(10, $data['data']);
        $this->assertEquals(25, $data['meta']['total']);
    }

    #[Test]
    public function it_can_search_comments_by_content()
    {
        Comment::factory()->create(['content' => 'This is a test comment']);
        Comment::factory()->create(['content' => 'Another comment']);

        $response = $this->get(route('admin.comment.data.index', [
            'query' => 'test',
        ]));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('test', $data[0]['content']);
    }

    #[Test]
    public function it_can_filter_comments_by_status()
    {
        Comment::factory()->create(['status' => 'active']);
        Comment::factory()->create(['status' => 'hidden']);
        Comment::factory()->create(['status' => 'deleted']);

        $response = $this->get(route('admin.comment.data.index', [
            'status' => 'active',
        ]));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('active', $data[0]['status']);
    }

    #[Test]
    public function it_can_get_single_comment_data()
    {
        $comment = Comment::factory()->create();

        $response = $this->get(route('admin.comment.data.show', $comment->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'content',
                'status',
                'created_at_human',
                'all_replies_count',
                'visible_replies_count',
                'likes_count',
            ],
        ]);

        $response->assertJson([
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'status' => $comment->status->value,
                'user_id' => $comment->user_id,
                'all_replies_count' => $comment->all_replies_count,
                'visible_replies_count' => $comment->visible_replies_count,
                'likes_count' => $comment->likes_count,
            ],
        ]);
    }
}
