<?php
// packages/truvoicer/tf-perspectives/src/Notifications/PerspectiveReacted.php

namespace Truvoicer\TfPerspectives\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Truvoicer\TfPerspectives\Models\Perspective;

class PerspectiveReacted extends Notification
{
    use Queueable;

    public function __construct(
        public Perspective $perspective,
        public $actor,
        public string $type,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $labels = [
            'empathy' => 'stood in your shoes',
            'insight' => 'found insight in your perspective',
            'relate'  => 'related to your perspective',
            'curious' => 'is curious about your perspective',
        ];

        return [
            'kind'                => 'reaction',
            'actor'               => [
                'id'     => $this->actor->id,
                'name'   => $this->actor->name,
                'handle' => $this->actor->handle ?? 'user-'.$this->actor->id,
            ],
            'perspective_id'      => $this->perspective->id,
            'perspective_excerpt' => mb_substr($this->perspective->body, 0, 120),
            'message'             => "{$this->actor->name} {$labels[$this->type]}.",
        ];
    }
}
