<?php
// packages/truvoicer/tf-perspectives/src/Notifications/ShoeDiscussed.php

namespace Truvoicer\TfPerspectives\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Truvoicer\TfPerspectives\Models\PerspectiveShoe;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;

class ShoeDiscussed extends Notification
{
    use Queueable;

    public function __construct(
        public ShoeDiscussion $discussion,
        public PerspectiveShoe $shoe,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $actor = $this->discussion->author;
        $actorName = $actor?->display_name ?? 'Someone';

        return [
            'kind'                => 'discussion',
            'actor'               => $actor ? [
                'id'     => $actor->id,
                'name'   => $actorName,
                'handle' => $actor->handle ?? 'user-'.$actor->id,
            ] : null,
            'perspective_id'      => $this->shoe->perspective_id,
            'perspective_excerpt' => mb_substr($this->discussion->body, 0, 120),
            'message'             => "{$actorName} replied to your pair of shoes.",
        ];
    }
}
