<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuenMatKhauMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(string $token)
    {
        $this->resetUrl = url('/admin/dat-lai-mat-khau/' . $token);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đặt lại mật khẩu - SmartMart Admin',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quen-mat-khau',
        );
    }
}
