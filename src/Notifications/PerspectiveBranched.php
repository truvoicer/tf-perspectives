<?php
// packages/truvoicer/tf-perspectives/src/Notifications/PerspectiveBranched.php

namespace Truvoicer\TfPerspectives\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Truvoicer\TfPerspectives\Models\Perspective;

class PerspectiveBranched extends Notification
{
    use Queueable;

    public function __construct(
        public Perspective $perspective,
        public $actor,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'kind'                => 'branch',
            'actor'               => [
                'id'     => $this->actor->id,
                'name'   => $this->actor->name,
                'handle' => $this->actor->handle ?? 'user-'.$this->actor->id,
            ],
            'perspective_id'      => $this->perspective->id,
            'perspective_excerpt' => mb_substr($this->perspective->body, 0, 120),
            'message'             => "{$this->actor->name} stepped into your shoes.",
        ];
    }
}
