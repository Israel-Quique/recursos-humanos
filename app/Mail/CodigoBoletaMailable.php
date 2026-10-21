<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CodigoBoletaMailable extends Mailable
{
    public function __construct(public string $nombre, public string $codigo) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Código de autenticación para generar tu boleta');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.codigo-boleta');
    }
}
