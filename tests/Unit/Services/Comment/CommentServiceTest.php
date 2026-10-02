<?php

// tests/Unit/Services/Comment/CommentServiceTest.php

namespace Truvoicer\TfPerspectives\Tests\Unit\Services\Comment;

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
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Repositories\CommentRepository;
use Truvoicer\TfPerspectives\Services\Comment\CommentService;
use Truvoicer\TfPerspectives\Services\SettingService;
use Truvoicer\TfPerspectives\Tests\TestCase;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentService $commentService;

    protected CommentRepository $commentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentService = app(CommentService::class);
        $this->commentRepository = app(CommentRepository::class);
    }

    #[Test]
    public function it_can_create_a_comment()
    {
        Event::fake();

        $user = User::factory()->create();

        $comment = $this->commentService->createComment(
            service: 'blog',
            provider: 'wordpress',
            itemId: 'post_123',
            content: 'Test comment',
            user: $user,
            ip: '127.0.0.1',
            userAgent: 'Mozilla/5.0'
        );

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Test comment',
            'user_id' => $user->getId(),
        ]);

        Event::assertDispatched(CommentCreated::class);
    }
    // ========== EDIT COMMENT TESTS ==========

    #[Test]
    public function it_can_edit_own_comment_within_window_using_settings()
    {
        Event::fake();

        // Set edit window to 30 minutes (default)
        SettingService::update([
            'enable_edit_comment_window' => true,
            'edit_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subMinutes(10), // Within window
        ]);

        $result = $this->commentService->updateComment($user, $comment, 'Updated content');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function it_cannot_edit_own_comment_outside_window_using_settings()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot edit this comment.');

        // Set edit window to 30 minutes
        SettingService::update([
            'enable_edit_comment_window' => true,
            'edit_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subHours(2), // Outside window
        ]);

        $this->commentService->updateComment($user, $comment, 'Updated content');
    }

    #[Test]
    public function it_can_edit_own_comment_without_time_limit_when_window_disabled_using_settings()
    {
        Event::fake();

        // Disable edit window
        SettingService::update([
            'enable_edit_comment_window' => false,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subYears(2), // Very old comment
        ]);

        $result = $this->commentService->updateComment($user, $comment, 'Updated content');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function it_can_edit_own_comment_with_custom_window_override()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subHours(2), // 2 hours old
        ]);

        // Override with custom 3-hour window
        $result = $this->commentService->updateComment($user, $comment, 'Updated content', false, 180);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function it_cannot_edit_own_comment_with_custom_window_override_when_outside_window()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot edit this comment.');

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subHours(2), // 2 hours old
        ]);

        // Override with custom 1-hour window (comment is outside)
        $this->commentService->updateComment($user, $comment, 'Updated content');
    }

    #[Test]
    public function it_can_edit_own_comment_without_time_limit_using_override()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subYears(2), // Very old comment
        ]);

        // Override with null window (no time limit)
        $result = $this->commentService->updateComment($user, $comment, 'Updated content', false);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function admin_can_edit_any_comment_regardless_of_settings()
    {
        Event::fake();

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subYears(2), // Very old comment
        ]);

        // Admin can edit even with settings enabled
        $result = $this->commentService->updateComment($admin, $comment, 'Admin updated content');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Admin updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function superuser_can_edit_any_comment_regardless_of_settings()
    {
        Event::fake();

        $superuser = User::factory()->create();
        $superuser->assignRole(Role::SUPERUSER);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subYears(2),
        ]);

        $result = $this->commentService->updateComment($superuser, $comment, 'Superuser updated content');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Superuser updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function user_cannot_edit_other_users_comment()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot edit this comment.');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user2->id,
            'content' => 'Original content',
        ]);

        $this->commentService->updateComment($user1, $comment, 'Updated content');
    }

    #[Test]
    public function it_can_edit_own_comment_with_default_settings_when_use_settings_is_null()
    {
        Event::fake();

        // Ensure settings are enabled with default 30-minute window
        SettingService::update([
            'enable_edit_comment_window' => true,
            'edit_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subMinutes(10), // Within window
        ]);

        $result = $this->commentService->updateComment($user, $comment, 'Updated content');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function it_uses_settings_by_default_when_use_settings_is_null()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot edit this comment.');

        // Settings enabled with 30-minute window
        SettingService::update([
            'enable_edit_comment_window' => true,
            'edit_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subHours(2), // Outside window
        ]);

        $this->commentService->updateComment($user, $comment, 'Updated content');
    }

    #[Test]
    public function it_can_edit_own_comment_with_30_minute_window_from_settings()
    {
        Event::fake();

        // Set custom 30-minute window
        SettingService::update([
            'enable_edit_comment_window' => true,
            'edit_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();

        // Create comment 20 minutes ago (within window)
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subMinutes(20),
        ]);

        $result = $this->commentService->updateComment($user, $comment, 'Updated content');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    #[Test]
    public function it_can_edit_own_comment_with_60_minute_window_from_settings()
    {
        Event::fake();

        // Set custom 60-minute window
        SettingService::update([
            'enable_edit_comment_window' => true,
            'edit_comment_window_minutes' => 60,
        ]);

        $user = User::factory()->create();

        // Create comment 45 minutes ago (within window)
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subMinutes(45),
        ]);

        $result = $this->commentService->updateComment($user, $comment, 'Updated content');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    #[Test]
    public function it_cannot_edit_own_comment_when_settings_window_is_expired()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot edit this comment.');

        // Set 30-minute window
        SettingService::update([
            'enable_edit_comment_window' => true,
            'edit_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();

        // Create comment 45 minutes ago (outside window)
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'created_at' => now()->subMinutes(45),
        ]);

        $this->commentService->updateComment($user, $comment, 'Updated content');
    }

    #[Test]
    public function it_updates_comment_content_and_triggers_event()
    {
        Event::fake();

        $user = User::factory()->create();
        $originalContent = 'This is the original comment content';
        $updatedContent = 'This is the updated comment content';

        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => $originalContent,
            'created_at' => now()->subMinutes(10),
        ]);

        $result = $this->commentService->updateComment($user, $comment, $updatedContent);

        $this->assertTrue($result);

        // Check content was updated
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => $updatedContent,
        ]);

        // Original content should not be present
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
            'content' => $originalContent,
        ]);

        // Status should remain active
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => $comment->status->value,
        ]);

        Event::assertDispatched(CommentUpdated::class);
    }

    #[Test]
    public function it_does_not_change_other_fields_when_updating()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => 'Original content',
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'status' => 'active',
            'is_approved' => true,
            'is_pinned' => false,
            'likes_count' => 5,
            'all_replies_count' => 2,
            'visible_replies_count' => 2,
            'created_at' => now()->subMinutes(10),
        ]);

        $originalData = $comment->toArray();

        $this->commentService->updateComment($user, $comment, 'Updated content');

        $updatedComment = $comment->fresh();

        // Content should be updated
        $this->assertEquals('Updated content', $updatedComment->content);

        // Other fields should remain unchanged
        $this->assertEquals($originalData['provider'], $updatedComment->provider);
        $this->assertEquals($originalData['service'], $updatedComment->service);
        $this->assertEquals($originalData['item_id'], $updatedComment->item_id);
        $this->assertEquals($originalData['status'], $updatedComment->status->value);
        $this->assertEquals($originalData['is_approved'], $updatedComment->is_approved);
        $this->assertEquals($originalData['is_pinned'], $updatedComment->is_pinned);
        $this->assertEquals($originalData['likes_count'], $updatedComment->likes_count);
        $this->assertEquals($originalData['all_replies_count'], $updatedComment->all_replies_count);
        $this->assertEquals($originalData['visible_replies_count'], $updatedComment->visible_replies_count);

        Event::assertDispatched(CommentUpdated::class);
    }

    // ========== DELETE COMMENT TESTS ==========

    #[Test]
    public function it_can_delete_own_comment_within_window_using_settings()
    {
        Event::fake();

        // Set delete window to 30 minutes (default)
        SettingService::update([
            'enable_delete_comment_window' => true,
            'delete_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subMinutes(10), // Within window
        ]);

        $result = $this->commentService->deleteComment($user, $comment, true);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
            'content' => '[This comment has been deleted]',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function it_cannot_delete_own_comment_outside_window_using_settings()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot delete this comment.');

        // Set delete window to 30 minutes
        SettingService::update([
            'enable_delete_comment_window' => true,
            'delete_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subHours(2), // Outside window
        ]);

        $this->commentService->deleteComment($user, $comment, true);
    }

    #[Test]
    public function it_can_delete_own_comment_without_time_limit_when_window_disabled_using_settings()
    {
        Event::fake();

        // Disable delete window
        SettingService::update([
            'enable_delete_comment_window' => false,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subYears(2), // Very old comment
        ]);

        $result = $this->commentService->deleteComment($user, $comment, true);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
            'content' => '[This comment has been deleted]',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function it_can_delete_own_comment_with_custom_window_override()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subHours(2), // 2 hours old
        ]);

        // Override with custom 3-hour window
        $result = $this->commentService->deleteComment(
            $user,
            $comment,
            false,
            180 // 3 hours
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
            'content' => '[This comment has been deleted]',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function it_cannot_delete_own_comment_with_custom_window_override_when_outside_window()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot delete this comment.');

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subHours(2), // 2 hours old
        ]);

        // Override with custom 1-hour window (comment is outside)
        $this->commentService->deleteComment(
            $user,
            $comment,
            false,
            60
        );
    }

    #[Test]
    public function it_can_delete_own_comment_without_time_limit_using_override()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subYears(2), // Very old comment
        ]);

        // Override with null window (no time limit)
        $result = $this->commentService->deleteComment(
            $user,
            $comment,
            false,
            null
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
            'content' => '[This comment has been deleted]',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function admin_can_delete_any_comment_regardless_of_settings()
    {
        Event::fake();

        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subYears(2), // Very old comment
        ]);

        // Admin can delete even with settings enabled
        $result = $this->commentService->deleteComment($admin, $comment, true);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
            'content' => '[This comment has been deleted]',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function superuser_can_delete_any_comment_regardless_of_settings()
    {
        Event::fake();

        $superuser = User::factory()->create();
        $superuser->assignRole(Role::SUPERUSER);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subYears(2),
        ]);

        $result = $this->commentService->deleteComment($superuser, $comment, true);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
            'content' => '[This comment has been deleted]',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function user_cannot_delete_other_users_comment()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot delete this comment.');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user2->id]);

        $this->commentService->deleteComment($user1, $comment);
    }

    #[Test]
    public function it_can_delete_own_comment_with_default_settings_when_use_settings_is_null()
    {
        Event::fake();

        // Ensure settings are enabled with default 30-minute window
        SettingService::update([
            'enable_delete_comment_window' => true,
            'delete_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subMinutes(10), // Within window
        ]);

        // useSettings = null (auto-detect) - should use settings
        $result = $this->commentService->deleteComment($user, $comment, null);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
            'content' => '[This comment has been deleted]',
        ]);

        Event::assertDispatched(CommentDeleted::class);
    }

    #[Test]
    public function it_can_delete_own_comment_with_30_minute_window_from_settings()
    {
        Event::fake();

        // Set custom 30-minute window
        SettingService::update([
            'enable_delete_comment_window' => true,
            'delete_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();

        // Create comment 20 minutes ago (within window)
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subMinutes(20),
        ]);

        $result = $this->commentService->deleteComment($user, $comment, true);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
        ]);
    }

    #[Test]
    public function it_can_delete_own_comment_with_60_minute_window_from_settings()
    {
        Event::fake();

        // Set custom 60-minute window
        SettingService::update([
            'enable_delete_comment_window' => true,
            'delete_comment_window_minutes' => 60,
        ]);

        $user = User::factory()->create();

        // Create comment 45 minutes ago (within window)
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subMinutes(45),
        ]);

        $result = $this->commentService->deleteComment($user, $comment, true);

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
        ]);
    }

    #[Test]
    public function it_cannot_delete_own_comment_when_settings_window_is_expired()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot delete this comment.');

        // Set 30-minute window
        SettingService::update([
            'enable_delete_comment_window' => true,
            'delete_comment_window_minutes' => 30,
        ]);

        $user = User::factory()->create();

        // Create comment 45 minutes ago (outside window)
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'created_at' => now()->subMinutes(45),
        ]);

        $this->commentService->deleteComment($user, $comment, true);
    }

    #[Test]
    public function it_soft_deletes_comment_and_replaces_content()
    {
        Event::fake();

        $user = User::factory()->create();
        $originalContent = 'This is the original comment content';
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'content' => $originalContent,
            'created_at' => now()->subMinutes(10),
        ]);

        $result = $this->commentService->deleteComment($user, $comment, true);

        $this->assertTrue($result);

        // Check content was replaced with deletion message
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => '[This comment has been deleted]',
        ]);

        // Original content should not be present
        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
            'content' => $originalContent,
        ]);

        // Status should be deleted
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'deleted',
        ]);
    }

    #[Test]
    public function it_can_toggle_comment_like()
    {
        Event::fake();

        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        // Like the comment
        $isLiked = $this->commentService->toggleCommentLike($comment, $user);

        $this->assertTrue($isLiked);
        $this->assertDatabaseHas('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->getId(),
        ]);
        $this->assertEquals(1, $comment->fresh()->likes_count);

        Event::assertDispatched(CommentLiked::class);

        // Unlike the comment
        $isLiked = $this->commentService->toggleCommentLike($comment, $user);

        $this->assertFalse($isLiked);
        $this->assertDatabaseMissing('comment_likes', [
            'comment_id' => $comment->id,
            'user_id' => $user->getId(),
        ]);
        $this->assertEquals(0, $comment->fresh()->likes_count);
    }

    #[Test]
    public function it_can_create_comment_report()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $report = $this->commentService->createCommentReport(
            comment: $comment,
            reason: 'spam',
            details: 'This is spam content',
            user: $user,
            ip: '127.0.0.1',
            userAgent: 'Mozilla/5.0'
        );

        $this->assertDatabaseHas('comment_reports', [
            'id' => $report->id,
            'comment_id' => $comment->id,
            'user_id' => $user->getId(),
            'reason' => 'spam',
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_cannot_create_duplicate_report_from_same_user()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You have already reported this comment.');

        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $this->commentService->createCommentReport($comment, 'spam', null, $user);
        $this->commentService->createCommentReport($comment, 'harassment', null, $user);
    }

    #[Test]
    public function it_can_update_comment_report()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();
        $report = $this->commentService->createCommentReport($comment, 'spam', null, $user);

        $updatedReport = $this->commentService->updateCommentReport($report, [
            'status' => 'reviewed',
            'reason' => 'harassment',
        ]);

        $this->assertEquals('reviewed', $updatedReport->status);
        $this->assertEquals('harassment', $updatedReport->reason);
    }

    #[Test]
    public function it_can_delete_comment_report()
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ADMIN);

        $comment = Comment::factory()->create();
        $report = $this->commentService->createCommentReport($comment, 'spam', null, $user);

        $result = $this->commentService->deleteCommentReport($report, $user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('comment_reports', ['id' => $report->id]);
    }

    #[Test]
    public function it_can_fetch_comment_replies()
    {
        $user = User::factory()->create();
        $parentComment = Comment::factory()->create([
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
        ]);

        // Create replies with specific created_at times to ensure order
        $reply1 = Comment::factory()->create([
            'parent_id' => $parentComment->id,
            'user_id' => $user->getId(),
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
            'created_at' => now()->subHours(2),
        ]);

        $reply2 = Comment::factory()->create([
            'parent_id' => $parentComment->id,
            'user_id' => $user->getId(),
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
            'created_at' => now()->subHours(1),
        ]);

        $reply3 = Comment::factory()->create([
            'parent_id' => $parentComment->id,
            'user_id' => $user->getId(),
            'status' => CommentStatus::ACTIVE,
            'is_approved' => true,
            'created_at' => now(),
        ]);

        $fetchedReplies = $this->commentService->fetchCommentReplies($parentComment);

        $this->assertCount(3, $fetchedReplies);
        $this->assertEquals($reply1->id, $fetchedReplies[0]->id);
        $this->assertEquals($reply2->id, $fetchedReplies[1]->id);
        $this->assertEquals($reply3->id, $fetchedReplies[2]->id);
    }

    #[Test]
    public function it_can_bulk_delete_comments()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $comments = Comment::factory()->count(3)->create();

        $deletedCount = $this->commentService->bulkDeleteComments(
            $comments->pluck('id')->toArray(),
            $admin
        );

        $this->assertEquals(3, $deletedCount);

        foreach ($comments as $comment) {
            $this->assertDatabaseHas('comments', [
                'id' => $comment->id,
                'status' => 'deleted',
                'content' => '[This comment has been deleted by moderator]',
            ]);
        }
    }
}
