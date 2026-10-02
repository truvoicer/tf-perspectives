<?php

// tests/Unit/Events/CommentEventsTest.php

namespace Truvoicer\TfPerspectives\Tests\Unit\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Truvoicer\TfPerspectives\Events\Comment\CommentCreated;
use Truvoicer\TfPerspectives\Events\Comment\CommentDeleted;
use Truvoicer\TfPerspectives\Events\Comment\CommentLiked;
use Truvoicer\TfPerspectives\Events\Comment\CommentUpdated;
use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

class CommentEventsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function comment_created_event_has_correct_structure()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
        ]);

        $event = new CommentCreated($comment);

        $this->assertEquals($comment->id, $event->comment->id);
        $this->assertEquals($comment->provider, $event->provider);
        $this->assertEquals($comment->service, $event->service);
        $this->assertEquals($comment->item_id, $event->itemId);

        $broadcastOn = $event->broadcastOn();
        $this->assertCount(2, $broadcastOn);
        $this->assertEquals('comments.blog.wordpress.post_123', $broadcastOn[0]->name);

        $broadcastWith = $event->broadcastWith();
        $this->assertArrayHasKey('comment', $broadcastWith);
        $this->assertEquals($comment->id, $broadcastWith['comment']['id']);
    }

    #[Test]
    public function comment_updated_event_has_correct_structure()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->getId()]);

        $event = new CommentUpdated($comment);

        $this->assertEquals($comment->id, $event->comment->id);
        $broadcastWith = $event->broadcastWith();
        $this->assertArrayHasKey('comment', $broadcastWith);
        $this->assertArrayHasKey('edited_at_human', $broadcastWith['comment']);
    }

    #[Test]
    public function comment_deleted_event_has_correct_structure()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->getId(),
            'parent_id' => null,
        ]);

        $event = new CommentDeleted($comment);

        $this->assertEquals($comment->id, $event->commentId);
        $this->assertEquals($comment->parent_id, $event->parentId);
        $this->assertEquals($comment->provider, $event->provider);
        $this->assertEquals($comment->service, $event->service);
        $this->assertEquals($comment->item_id, $event->itemId);

        $broadcastWith = $event->broadcastWith();
        $this->assertEquals($comment->id, $broadcastWith['comment_id']);
    }

    #[Test]
    public function comment_liked_event_has_correct_structure()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        $isLiked = true;
        $event = new CommentLiked($comment, $user->getId(), $isLiked);

        $this->assertEquals($comment->id, $event->commentId);
        $this->assertEquals($comment->likes_count, $event->likesCount);
        $this->assertEquals($user->getId(), $event->userId);
        $this->assertEquals($isLiked, $event->isLiked);

        $broadcastWith = $event->broadcastWith();
        $this->assertEquals($comment->id, $broadcastWith['comment_id']);
        $this->assertEquals($user->getId(), $broadcastWith['user_id']);
        $this->assertEquals($isLiked, $broadcastWith['is_liked']);
    }

    #[Test]
    public function comment_events_are_broadcast_on_correct_channels()
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'provider' => 'blog',
            'service' => 'wordpress',
            'item_id' => 'post_123',
        ]);

        $createdEvent = new CommentCreated($comment);
        $channels = $createdEvent->broadcastOn();

        $this->assertInstanceOf(Channel::class, $channels[0]);
        $this->assertEquals('comments.blog.wordpress.post_123', $channels[0]->name);
        $this->assertInstanceOf(PrivateChannel::class, $channels[1]);
        $this->assertEquals("private-user.{$comment->user_id}", $channels[1]->name);
    }
}
