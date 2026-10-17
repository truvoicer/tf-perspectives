<?php
// packages/truvoicer/tf-perspectives/src/Notifications/ShoeAdded.php

namespace Truvoicer\TfPerspectives\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Truvoicer\TfPerspectives\Models\PerspectiveShoe;

class ShoeAdded extends Notification
{
    use Queueable;

    public function __construct(public PerspectiveShoe $shoe) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $actor = $this->shoe->author;
        $actorName = $actor?->display_name ?? 'Someone';

        return [
            'kind'                => 'shoe',
            'actor'               => $actor ? [
                'id'     => $actor->id,
                'name'   => $actorName,
                'handle' => $actor->handle ?? 'user-'.$actor->id,
            ] : null,
            'perspective_id'      => $this->shoe->perspective_id,
            'perspective_excerpt' => mb_substr($this->shoe->body, 0, 120),
            'message'             => "{$actorName} stepped into your shoes.",
        ];
    }
}
