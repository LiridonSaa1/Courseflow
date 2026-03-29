<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentInvited extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $inviteeEmail,
        public string $loginUrl,
        public string $temporaryPassword,
        public string $workspaceName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Invitation to :workspace', ['workspace' => $this->workspaceName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.student-invited',
        );
    }
}
