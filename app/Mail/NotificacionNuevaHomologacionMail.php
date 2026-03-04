<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionNuevaHomologacionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // 🔥 ESTO ES LO QUE TE FALTABA
    public $timeout = 180; // segundos
    public $tries = 3;

    public $proveedor;
    public $nombreSoporte;
    public $correoSoporte;

    public function __construct($proveedor, $nombreSoporte, $correoSoporte)
    {
        $this->proveedor        = $proveedor;
        $this->nombreSoporte    = $nombreSoporte;
        $this->correoSoporte    = $correoSoporte;
    }
    public function build()
    {
        return $this->subject('Se ha creado una nueva homologación')
            ->view('emails.notificacion_nueva_homologacion');
    }
}
