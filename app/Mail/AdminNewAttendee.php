<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewAttendee extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Attendee $attendee) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Attendee Registration: ' . $this->attendee->registration_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-new-attendee');
    }
}
