<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Events\CorreoHomologacionEnviado; // 👈 IMPORTAR EVENTO

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
    public $idpersona_facha_homologacion ;

    public function __construct($proveedor, $documentos, $nombreSoporte, $correoSoporte,$idpersona_facha_homologacion )
    {
        $this->proveedor        = $proveedor;
        $this->documentos       = $documentos;
        $this->nombreSoporte    = $nombreSoporte;
        $this->correoSoporte    = $correoSoporte;
         $this->idpersona_facha_homologacion    = $idpersona_facha_homologacion ;
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
        /**
     * 🔥 ESTE ES EL TRUCO - SOBREESCRIBIR send()
     */
    public function send($mailer)
    {
        // 1. Enviar el correo
        parent::send($mailer);
        
        // 2. DISPARAR EL EVENTO 🚀
        if ($this->idpersona_facha_homologacion ) {
            CorreoHomologacionEnviado::dispatch(
                $this->idpersona_facha_homologacion ,
                $this->correoSoporte,
                [
                    'asunto' => $this->subject,
                    'proveedor' => $this->proveedor->nombre ?? null,
                    'documentos_count' => count($this->documentos ?? [])
                ]
            );
        }
    }
}
