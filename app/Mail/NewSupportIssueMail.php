<?php

namespace App\Mail;

use App\Models\SupportIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSupportIssueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportIssue $issue) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New support issue: '.$this->issue->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.support-issue-created');
    }
}
