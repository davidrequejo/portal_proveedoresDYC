<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProveedorActualizadoLogisticaMail extends Mailable  implements ShouldQueue
{
    use Queueable, SerializesModels;

    // 🔥 ESTO ES LO QUE TE FALTABA
    public $timeout = 180; // segundos
    public $tries = 3;

    public $data;  // Esto puede ser cualquier dato (proveedor, cuenta bancaria, cliente)
    public $tipo;  // Tipo de la notificación (proveedor, cuenta_bancaria, cliente)

    /**
     * Crear una nueva instancia de mensaje.
     *
     * @param  mixed  $data
     * @param  string  $tipo
     * @return void
     */
    public function __construct($data, $tipo)
    {
        $this->data = $data;
        $this->tipo = $tipo;
    }

    /**
     * Construir el mensaje.
     *
     * @return \Illuminate\Mail\MailMessage
     */
    public function build()
    {
        // Aquí verificamos el tipo y la acción para personalizar el mensaje
        if ($this->tipo == 'proveedor') {
            $subject = 'Actualización de datos del proveedor';
            $view = 'emails.proveedor_actualizado'; // Esta es la vista del proveedor
        }  elseif ($this->tipo == 'cliente') {
            $subject = 'Actualización de datos del cliente';
            $view = 'emails.proveedor_actualizado'; // Esta es la vista para cliente
        }

        // Ahora usamos el tipo y la acción para enviar el correo adecuado
        return $this->subject($subject)
                    ->view($view, ['data' => $this->data]);
    }
}
