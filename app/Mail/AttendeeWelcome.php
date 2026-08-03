<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendeeWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Attendee $attendee) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your IDIEXPO ' . ($this->attendee->expo->year ?? '2026') . ' Registration Confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.attendee-welcome');
    }
}
