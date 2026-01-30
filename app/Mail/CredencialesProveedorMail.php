<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesProveedorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

     // 🔥 ESTO ES LO QUE TE FALTABA
    public $timeout = 180; // segundos
    public $tries = 3;

    public function __construct(
        public string $nombre,
        public string $usuario,
        public string $clave,
        public string $correoSoporte,
        public string $nombreSoporte
    ) {}

    public function build()
    {
        return $this->subject('Acceso al Portal de Proveedores')
            ->markdown('emails.proveedor.credenciales');
    }
}

