<?php

// tests/Feature/Admin/Comment/Report/AdminCommentReportControllerTest.php

namespace Truvoicer\TfPerspectives\Tests\Feature\Http\Controllers\Admin\Comment\Report;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Enums\Page\DefaultPage;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\CommentReport;
use Truvoicer\TfPerspectives\Models\Page;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class AdminCommentReportControllerTest extends TestCase
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
            'slug' => DefaultPage::ADMIN_COMMENT_REPORTS->value,
            'is_active' => true,
            'title' => 'Admin Comment Reports',
            'content' => 'Test content',
        ]);

        $this->actingAs($this->admin);
    }

    // ========== FULL PAGE LOAD TESTS (Using Inertia Assertions) ==========

    #[Test]
    public function it_returns_all_props_for_initial_page_load()
    {
        $user = User::factory()->create();
        CommentReport::factory()->count(3)->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        $response = $this->get(route('admin.comment.report.index', $this->comment->id));

        $response->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('page')
                    ->missing('comment')
                    ->missing('reports')
            );
    }

    #[Test]
    public function it_returns_only_requested_props_for_partial_reload()
    {
        $user = User::factory()->create();
        CommentReport::factory()->count(3)->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'reports',
        ])->get(route('admin.comment.report.index', $this->comment->id));

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');

        $data = $response->json();

        $this->assertEquals('admin/comment/report/index', $data['component']);

        // Requested props should be present
        $this->assertArrayHasKey('reports', $data['props']);
        $this->assertArrayHasKey('data', $data['props']['reports']);
        $this->assertCount(3, $data['props']['reports']['data']);

        // Non-requested props should be missing
        $this->assertArrayNotHasKey('page', $data['props']);
        $this->assertArrayNotHasKey('comment', $data['props']);
    }

    #[Test]
    public function it_returns_multiple_requested_props_for_partial_reload()
    {
        $user = User::factory()->create();
        CommentReport::factory()->count(3)->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'reports,comment',
        ])->get(route('admin.comment.report.index', $this->comment->id));

        $response->assertOk();

        $data = $response->json();

        $this->assertEquals('admin/comment/report/index', $data['component']);

        // Both requested props should be present
        $this->assertArrayHasKey('reports', $data['props']);
        $this->assertArrayHasKey('comment', $data['props']);

        // Non-requested prop should be missing
        $this->assertArrayNotHasKey('page', $data['props']);
    }

    #[Test]
    public function it_returns_optional_props_only_when_partial_data_includes_them()
    {
        $user = User::factory()->create();
        CommentReport::factory()->count(3)->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        // Request that does NOT include 'reports' in partial data
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'comment',
        ])->get(route('admin.comment.report.index', $this->comment->id));

        $response->assertOk();

        $data = $response->json();

        $this->assertEquals('admin/comment/report/index', $data['component']);

        // Only requested prop should be present
        $this->assertArrayHasKey('comment', $data['props']);

        // Non-requested props should be missing
        $this->assertArrayNotHasKey('reports', $data['props']);
        $this->assertArrayNotHasKey('page', $data['props']);
    }

    #[Test]
    public function it_returns_empty_array_for_optional_props_when_no_data()
    {
        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'reports',
        ])->get(route('admin.comment.report.index', $this->comment->id));

        $response->assertOk();

        $data = $response->json();

        $this->assertEquals('admin/comment/report/index', $data['component']);

        // Reports prop should exist but be empty
        $this->assertArrayHasKey('reports', $data['props']);
        $this->assertArrayHasKey('data', $data['props']['reports']);
        $this->assertCount(0, $data['props']['reports']['data']);
        $this->assertEquals(0, $data['props']['reports']['meta']['total']);

        // Other props should be missing
        $this->assertArrayNotHasKey('page', $data['props']);
        $this->assertArrayNotHasKey('comment', $data['props']);
    }

    #[Test]
    public function it_handles_multiple_partial_requests_with_different_props()
    {
        $user = User::factory()->create();
        CommentReport::factory()->count(3)->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        // First partial: request reports only
        $response1 = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'reports',
        ])->get(route('admin.comment.report.index', $this->comment->id));

        $data1 = $response1->json();
        $this->assertArrayHasKey('reports', $data1['props']);
        $this->assertArrayNotHasKey('comment', $data1['props']);
        $this->assertArrayNotHasKey('page', $data1['props']);

        // Second partial: request comment only
        $response2 = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'comment',
        ])->get(route('admin.comment.report.index', $this->comment->id));

        $data2 = $response2->json();
        $this->assertArrayHasKey('comment', $data2['props']);
        $this->assertArrayNotHasKey('reports', $data2['props']);
        $this->assertArrayNotHasKey('page', $data2['props']);
    }

    #[Test]
    public function it_returns_paginated_reports_for_partial_reload()
    {
        $user = User::factory()->create();
        CommentReport::factory()->count(25)->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
        ]);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'reports',
        ])->get(route('admin.comment.report.index', [
            'comment' => $this->comment->id,
            'per_page' => 10,
            'page' => 2,
        ]));

        $response->assertOk();

        $data = $response->json();

        $this->assertArrayHasKey('reports', $data['props']);
        $this->assertCount(10, $data['props']['reports']['data']);
        $this->assertEquals(2, $data['props']['reports']['meta']['current_page']);
        $this->assertEquals(10, $data['props']['reports']['meta']['per_page']);
        $this->assertEquals(25, $data['props']['reports']['meta']['total']);
        $this->assertEquals(3, $data['props']['reports']['meta']['last_page']);
    }

    #[Test]
    public function it_filters_reports_by_status_for_partial_reload()
    {
        $user = User::factory()->create();

        CommentReport::factory()->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'status' => 'pending',
        ]);
        CommentReport::factory()->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'status' => 'reviewed',
        ]);
        CommentReport::factory()->create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'status' => 'dismissed',
        ]);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'reports',
        ])->get(route('admin.comment.report.index', [
            'comment' => $this->comment->id,
            'status' => 'pending',
        ]));

        $response->assertOk();

        $data = $response->json();

        $this->assertCount(1, $data['props']['reports']['data']);
        $this->assertEquals('pending', $data['props']['reports']['data'][0]['status']);
    }

    #[Test]
    public function it_searches_reports_by_reason_for_partial_reload()
    {
        $user = User::factory()->create();

        CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam content',
            'details' => 'This is spam',
            'status' => 'pending',
        ]);
        CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'harassment',
            'details' => 'Harassing content',
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'admin/comment/report/index',
            'X-Inertia-Partial-Data' => 'reports',
        ])->get(route('admin.comment.report.index', [
            'comment' => $this->comment->id,
            'query' => 'spam',
        ]));

        $response->assertOk();

        $data = $response->json();

        $this->assertCount(1, $data['props']['reports']['data']);
        $this->assertEquals('spam content', $data['props']['reports']['data'][0]['reason']);
    }
    // ========== CREATE REPORT TESTS ==========

    #[Test]
    public function it_displays_create_report_page()
    {
        $response = $this->get(route('admin.comment.report.create', $this->comment->id));

        $response->assertOk();
        $response->assertHeaderMissing('X-Inertia');
        $this->assertStringContainsString('<!DOCTYPE html>', $response->getContent());
    }

    #[Test]
    public function it_creates_a_new_comment_report()
    {
        $user = User::factory()->create();

        $reportData = [
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'details' => 'This is spam content',
            'status' => 'pending',
        ];

        $response = $this->post(route('admin.comment.report.store', $this->comment->id), $reportData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment report created successfully.');

        $this->assertDatabaseHas('comment_reports', [
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'details' => 'This is spam content',
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_creates_a_report_without_user_id_for_guest_reports()
    {
        $reportData = [
            'reason' => 'spam',
            'details' => 'This is spam content',
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'status' => 'pending',
        ];

        $response = $this->post(route('admin.comment.report.store', $this->comment->id), $reportData);

        $response->assertRedirect();

        $response->assertSessionHasErrors(['user_id', 'comment_id']);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_report()
    {
        $response = $this->post(route('admin.comment.report.store', $this->comment->id), []);

        $response->assertSessionHasErrors(['reason']);
    }

    #[Test]
    public function it_validates_reason_is_required()
    {
        $response = $this->post(route('admin.comment.report.store', $this->comment->id), [
            'details' => 'Some details',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    #[Test]
    public function it_validates_reason_has_valid_value()
    {
        $response = $this->post(route('admin.comment.report.store', $this->comment->id), [
            'reason' => 'invalid_reason',
            'details' => 'Some details',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    #[Test]
    public function it_validates_email_format_when_provided()
    {
        $response = $this->post(route('admin.comment.report.store', $this->comment->id), [
            'reason' => 'spam',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function it_validates_details_max_length()
    {
        $response = $this->post(route('admin.comment.report.store', $this->comment->id), [
            'reason' => 'spam',
            'details' => str_repeat('a', 5001), // Assuming max 5000 characters
        ]);

        $response->assertSessionHasErrors(['details']);
    }

    // ========== UPDATE REPORT TESTS ==========

    #[Test]
    public function it_displays_edit_report_page()
    {
        $user = User::factory()->create();
        $report = CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'details' => 'This is spam content',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->get(route('admin.comment.report.edit', [
            'comment' => $this->comment->id,
            'commentReport' => $report->id,
        ]));

        $response->assertOk();
        $response->assertHeaderMissing('X-Inertia');
        $this->assertStringContainsString('<!DOCTYPE html>', $response->getContent());
    }

    #[Test]
    public function it_updates_a_comment_report()
    {
        $user = User::factory()->create();
        $report = CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'details' => 'Original details',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $updateData = [
            'reason' => 'harassment',
            'details' => 'Updated details',
            'status' => 'reviewed',
        ];

        $response = $this->patch(route('admin.comment.report.update', [
            'comment' => $this->comment->id,
            'commentReport' => $report->id,
        ]), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment report updated successfully.');

        $this->assertDatabaseHas('comment_reports', [
            'id' => $report->id,
            'reason' => 'harassment',
            'details' => 'Updated details',
            'status' => 'reviewed',
        ]);
    }

    #[Test]
    public function it_updates_only_status_of_a_report()
    {
        $user = User::factory()->create();
        $report = CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'details' => 'Original details',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->patch(route('admin.comment.report.update', [
            'comment' => $this->comment->id,
            'commentReport' => $report->id,
        ]), [
            'status' => 'dismissed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment report updated successfully.');

        $this->assertDatabaseHas('comment_reports', [
            'id' => $report->id,
            'reason' => 'spam', // Should remain unchanged
            'details' => 'Original details', // Should remain unchanged
            'status' => 'dismissed',
        ]);
    }

    #[Test]
    public function it_validates_reason_when_updating_report()
    {
        $user = User::factory()->create();
        $report = CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->patch(route('admin.comment.report.update', [
            'comment' => $this->comment->id,
            'commentReport' => $report->id,
        ]), [
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    #[Test]
    public function it_validates_status_when_updating_report()
    {
        $user = User::factory()->create();
        $report = CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->patch(route('admin.comment.report.update', [
            'comment' => $this->comment->id,
            'commentReport' => $report->id,
        ]), [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    #[Test]
    public function it_deletes_a_report()
    {
        $report = CommentReport::create([
            'comment_id' => $this->comment->id,
            'user_id' => $this->admin->id,
            'reason' => 'spam',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->delete(route('admin.comment.report.destroy', [
            'comment' => $this->comment->id,
            'commentReport' => $report->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Comment report deleted successfully.');

        $this->assertDatabaseMissing('comment_reports', ['id' => $report->id]);
    }

    #[Test]
    public function it_cannot_delete_report_that_does_not_belong_to_comment()
    {
        $otherComment = Comment::factory()->create();
        $report = CommentReport::create([
            'comment_id' => $otherComment->id,
            'user_id' => $this->admin->id,
            'reason' => 'spam',
            'status' => 'pending',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->delete(route('admin.comment.report.destroy', [
            'comment' => $this->comment->id,
            'commentReport' => $report->id,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This report does not belong to this comment.');
    }

    #[Test]
    public function it_bulk_deletes_reports()
    {
        $reports = CommentReport::factory()->count(3)->create([
            'comment_id' => $this->comment->id,
        ]);
        $reportIds = $reports->pluck('id')->toArray();

        $response = $this->post(route('admin.comment.report.bulk.destroy', ['comment' => $this->comment->id]), [
            'ids' => $reportIds,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Selected comment reports deleted successfully.');

        foreach ($reports as $report) {
            $this->assertDatabaseMissing('comment_reports', ['id' => $report->id]);
        }
    }

    #[Test]
    public function it_validates_bulk_delete_request()
    {
        $response = $this->post(route('admin.comment.report.bulk.destroy', ['comment' => $this->comment->id]), [
            'ids' => ['invalid'],
        ]);

        $response->assertSessionHasErrors(['ids.0']);
    }
}
