<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionDocumentosHomologacionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // 🔥 ESTO ES LO QUE TE FALTABA
    public $timeout = 180; // segundos
    public $tries = 3;

    public $proveedor;
    public $documentos;
    public $usuarioLogistica;

    public function __construct($proveedor, $documentos, $usuarioLogistica)
    {
        $this->proveedor        = $proveedor;
        $this->documentos       = $documentos;
        $this->usuarioLogistica = $usuarioLogistica;
    }

    /*public function build()
    {
        return $this->subject('Resultado de revisión de documentos de homologación')
            ->replyTo(
                $this->usuarioLogistica->email,
                $this->usuarioLogistica->name
            )
            ->view('emails.notificacion_documentos_homologacion');
    }*/
    public function build()
    {
        return $this->subject('Resultado de revisión de documentos de homologación')
            ->view('emails.notificacion_documentos_homologacion');
    }
}
