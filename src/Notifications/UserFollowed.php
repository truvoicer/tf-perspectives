<?php
// packages/truvoicer/tf-perspectives/src/Notifications/UserFollowed.php

namespace Truvoicer\TfPerspectives\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserFollowed extends Notification
{
    use Queueable;

    public function __construct(public $actor) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'  => 'follow',
            'actor' => [
                'id'     => $this->actor->id,
                'name'   => $this->actor->name,
                'handle' => $this->actor->handle ?? 'user-'.$this->actor->id,
            ],
            'perspective_id'      => 0,
            'perspective_excerpt' => '',
            'message'             => "{$this->actor->name} started following you.",
        ];
    }
}
