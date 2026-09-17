<?php

namespace App\Mail;

use App\Models\Empleado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComunicadoPersonalMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Empleado $empleado,
        public string $asunto,
        public string $mensaje,
        public string $alcanceLabel = 'Institucional Masivo'
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comunicado-personal',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
