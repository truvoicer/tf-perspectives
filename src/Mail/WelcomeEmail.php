<?php
// packages/truvoicer/tf-perspectives/src/Mail/WelcomeEmail.php

namespace Truvoicer\TfPerspectives\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Other Shoes',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'tf-perspectives::emails.welcome',
            with: [
                'name' => $this->user->first_name ?: $this->user->display_name,
                'url'  => route('perspectives.index'),
            ],
        );
    }
}
