<?php

// tests/Feature/Admin/Comment/Report/Data/AdminCommentReportDataControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Admin\Comment\Report\Data;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\CommentReport;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class AdminCommentReportDataControllerTest extends TestCase
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
    public function it_can_get_paginated_comment_reports_data()
    {

        CommentReport::factory()->count(25)->create(['comment_id' => $this->comment->id]);

        $response = $this->get(route('admin.comment.report.data.index', [
            'comment' => $this->comment->id,
            'per_page' => 10,
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'user_id', 'comment_id', 'reason', 'status', 'created_at'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            'links',
        ]);

        $data = $response->json();
        $this->assertCount(10, $data['data']);
        $this->assertEquals(25, $data['meta']['total']);
    }

    #[Test]
    public function it_can_filter_comment_reports_by_reason()
    {
        CommentReport::factory()->create([
            'comment_id' => $this->comment->id,
            'reason' => 'spam',
        ]);
        CommentReport::factory()->create([
            'comment_id' => $this->comment->id,
            'reason' => 'harassment',
        ]);

        $response = $this->get(route('admin.comment.report.data.index', [
            'comment' => $this->comment->id,
            'reason' => 'spam',
        ]));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('spam', $data[0]['reason']);
    }

    #[Test]
    public function it_can_filter_comment_reports_by_status()
    {
        CommentReport::factory()->create([
            'comment_id' => $this->comment->id,
            'status' => 'pending',
        ]);
        CommentReport::factory()->create([
            'comment_id' => $this->comment->id,
            'status' => 'reviewed',
        ]);

        $response = $this->get(route('admin.comment.report.data.index', [
            'comment' => $this->comment->id,
            'status' => 'pending',
        ]));

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pending', $data[0]['status']);
    }
}
