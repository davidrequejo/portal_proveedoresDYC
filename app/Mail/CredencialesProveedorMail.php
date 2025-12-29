<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredencialesProveedorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nombre,
        public string $usuario,
        public string $clave
    ) {}

    public function build()
    {
        return $this->subject('Acceso al Portal de Proveedores')
            ->markdown('emails.proveedor.credenciales');
    }
}

