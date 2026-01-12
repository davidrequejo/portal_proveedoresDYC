<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstadoDocumentoLogisticaMail extends Mailable
{
    use Queueable, SerializesModels;

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