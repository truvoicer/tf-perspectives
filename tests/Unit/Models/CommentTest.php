<?php

// tests/Unit/Models/CommentTest.php

namespace Truvoicer\TfPerspectives\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Enums\Auth\Role\Role;
use Truvoicer\TfPerspectives\Enums\Comment\CommentStatus;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_comment_test()
    {
        $user = User::factory()->create();

        $comment = Comment::create([
            'user_id' => $user->getId(),
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'content' => 'This is a test comment',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'This is a test comment',
            'provider' => 'blog',
        ]);
    }

    #[Test]
    public function it_calculates_depth_and_path_for_nested_comments()
    {
        $user = User::factory()->create();

        $parent = Comment::create([
            'user_id' => $user->getId(),
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'content' => 'Parent comment',
        ]);

        $child = Comment::create([
            'user_id' => $user->getId(),
            'parent_id' => $parent->id,
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
            'content' => 'Child comment',
        ]);

        $this->assertEquals(0, $parent->depth);
        $this->assertEquals(1, $child->depth);
        $this->assertEquals('', $parent->path);
        $this->assertStringContainsString($parent->id, $child->path);
    }

    #[Test]
    public function it_has_relationships()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->getId()]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($user->getId(), $comment->user->id);
    }

    #[Test]
    public function it_can_check_if_user_can_edit_comment()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);
        $otherUser = User::factory()->create();

        $comment = Comment::factory()->create([
            'user_id' => $owner->id,
            'created_at' => now()->subMinutes(10),
        ]);

        $this->assertTrue($comment->canEdit($owner));
        $this->assertTrue($comment->canEdit($admin));
        $this->assertFalse($comment->canEdit($otherUser));

        // Test edit window (30 minutes)
        $oldComment = Comment::factory()->create([
            'user_id' => $owner->id,
            'created_at' => now()->subHours(2),
        ]);

        $this->assertTrue($oldComment->canEdit($owner));
    }

    #[Test]
    public function it_can_check_if_user_can_delete_comment()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);
        $otherUser = User::factory()->create();

        $comment = Comment::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($comment->canDelete($owner));
        $this->assertTrue($comment->canDelete($admin));
        $this->assertFalse($comment->canDelete($otherUser));
    }

    #[Test]
    public function it_scopes_approved_comments()
    {
        Comment::factory()->create(['is_approved' => true]);
        Comment::factory()->create(['is_approved' => false]);

        $approvedComments = Comment::approved()->get();

        $this->assertCount(1, $approvedComments);
        $this->assertTrue($approvedComments->first()->is_approved);
    }

    #[Test]
    public function it_scopes_active_comments()
    {
        Comment::factory()->create(['status' => CommentStatus::ACTIVE]);
        Comment::factory()->create(['status' => CommentStatus::HIDDEN]);
        Comment::factory()->create(['status' => CommentStatus::DELETED]);

        $activeComments = Comment::active()->get();

        $this->assertCount(1, $activeComments);
        $this->assertEquals(CommentStatus::ACTIVE, $activeComments->first()->status);
    }

    #[Test]
    public function it_scopes_root_comments()
    {
        Comment::factory()->create(['parent_id' => null]);
        Comment::factory()->create(['parent_id' => 1]);

        $rootComments = Comment::rootComments()->get();

        $this->assertCount(1, $rootComments);
        $this->assertNull($rootComments->first()->parent_id);
    }
}
