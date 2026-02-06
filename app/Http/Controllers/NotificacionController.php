<?php

namespace App\Http\Controllers;

class NotificacionController extends Controller
{
    /**
     * Listado de todas las notificaciones
     */
// Marcar una notificación como leída
    public function leer($id)
    {
        $n = auth()->user()
            ->notifications()
            ->findOrFail($id);

        if ($n->read_at === null) {
            $n->markAsRead();
        }

        return back(); // 👈 vuelve al mismo navbar
    }

    // Marcar todas como leídas
    public function marcarTodas()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }
}
