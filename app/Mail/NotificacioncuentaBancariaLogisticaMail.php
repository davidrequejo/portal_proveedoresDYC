<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacioncuentaBancariaLogisticaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // 🔥 ESTO ES LO QUE TE FALTABA
    public $timeout = 180; // segundos
    public $tries = 3;

    public $data;  // Esto puede ser cualquier dato (proveedor, cuenta bancaria, cliente)
    public $cuenta;  // Esto puede ser cualquier dato (proveedor, cuenta bancaria, cliente)
    public $tipo;  // Tipo de la notificación (proveedor, cuenta_bancaria, cliente)
    public $accion;  // Acción de la notificación (agregar, actualizar)

    /**
     * Crear una nueva instancia de mensaje.
     *
     * @param  mixed  $data
     * @param  mixed  $cuenta
     * @param  string  $tipo
     * @param  string  $accion
     * @return void
     */
    public function __construct($data, $cuenta, $tipo, $accion)
    {
        $this->data = $data;
        $this->cuenta = $cuenta;
        $this->tipo = $tipo;
        $this->accion = $accion;
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

            if ($this->accion == 'agregar') {
              $subject = 'Proveedor agrego nueva cuenta bancaria';
              $view = 'emails.notificacion_cuenta_bancaria'; // Esta es la vista del proveedor
            }elseif ( $this->accion == 'editar') {
             $subject = 'Proveedor actualizó cuenta bancaria';
             $view = 'emails.notificacion_cuenta_bancaria'; // Esta es la vista para proveedor
            }elseif ( $this->accion == 'desactivar') {
             $subject = 'Proveedor desactivó cuenta bancaria';
             $view = 'emails.notificacion_cuenta_bancaria'; // Esta es la vista para proveedor
            }

        }  elseif ($this->tipo == 'cliente') {

            if ($this->accion == 'agregar') {
              $subject = 'Cliente agrego nueva cuenta bancaria';
              $view = 'emails.notificacion_cuenta_bancaria'; // Esta es la vista del cliente
            }elseif ( $this->accion == 'editar') {
             $subject = 'Cliente actualizó cuenta bancaria';
             $view = 'emails.notificacion_cuenta_bancaria'; // Esta es la vista para cliente
            }elseif ( $this->accion == 'desactivar') {
             $subject = 'Cliente desactivó cuenta bancaria';
             $view = 'emails.notificacion_cuenta_bancaria'; // Esta es la vista para cliente
            }
        }

        // Ahora usamos el tipo y la acción para enviar el correo adecuado
        return $this->subject($subject)
                    ->view($view, ['data' => $this->data, 'cuenta' => $this->cuenta]);
    }
}
