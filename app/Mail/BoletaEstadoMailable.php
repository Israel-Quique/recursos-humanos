<?php

namespace App\Mail;

use App\Models\PermisoLaboral;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoletaEstadoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PermisoLaboral $permiso,
        public string $estado,
        public ?string $motivoRechazo = null
    ) {
    }

    public function envelope(): Envelope
    {
        $nombre = trim($this->permiso->empleado?->nombre_completo ?? '');
        if ($nombre === '') {
            $nombre = 'Funcionario';
        }
        $esAprobado = $this->estado === 'aprobado';

        $asunto = $esAprobado
            ? "✅ Solicitud de Boleta Aprobada - {$nombre}"
            : "❌ Notificación de Boleta Rechazada - {$nombre}";

        return new Envelope(
            subject: $asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.boleta-estado',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
