<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstadoDocumentoLogisticaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    // 🔥 ESTO ES LO QUE TE FALTABA
    public $timeout = 180; // segundos
    public $tries = 3;

    public $proveedor;
    public $documento;
    public $estado;
    public $observacion;
    public $usuarioLogistica;

    public function __construct($proveedor, $documento, $estado_revision, $observacion, $usuarioLogistica)

    {
        $this->proveedor        = $proveedor;
        $this->documento        = $documento;
        $this->estado           = $estado_revision;
        $this->observacion      = $observacion;
        $this->usuarioLogistica = $usuarioLogistica;
    }

    public function build()
    {
        return $this->subject('Estado de documento actualizado')
            ->replyTo(
                $this->usuarioLogistica->email,
                $this->usuarioLogistica->name
            )
            ->view('emails.estado_documento_logistica');
    }
}