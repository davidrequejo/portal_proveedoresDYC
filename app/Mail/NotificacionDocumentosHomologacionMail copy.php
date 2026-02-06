<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

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
    public $nombreSoporte;
    public $correoSoporte;

    public function __construct($proveedor, $documentos, $nombreSoporte, $correoSoporte)
    {
        $this->proveedor        = $proveedor;
        $this->documentos       = $documentos;
        $this->nombreSoporte    = $nombreSoporte;
        $this->correoSoporte    = $correoSoporte;
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
