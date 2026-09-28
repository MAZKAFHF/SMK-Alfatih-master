<?php

namespace App\Mail;

use App\Models\PPDBRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $template,
        public array $payload = [],
        public ?PPDBRegistration $application = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.ppdb.generic',
            text: 'emails.ppdb.generic-text',
            with: [
                'template' => $this->template,
                'payload' => $this->payload,
                'application' => $this->application,
            ],
        );
    }
}
