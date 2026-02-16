<?php

namespace App\Listeners;

use App\Events\CorreoHomologacionEnviado;
use Illuminate\Support\Facades\Log;
use App\Models\RegistrarNotifiHomolog_fph;

class RegistrarNotificacionHomologacion
{
    public function handle(CorreoHomologacionEnviado $event)
    {
        Log::info('📦 DATOS DEL EVENTO', [
            'idpersona_facha_homologacion' => $event->idpersona_facha_homologacion,
            'correo' => $event->correo,
            'metadata' => $event->metadata
        ]);
        
        try {
            RegistrarNotifiHomolog_fph::create([
                'idpersona_facha_homologacion' => $event->idpersona_facha_homologacion,
                'estado' => 'enviado',
                'fecha_envio' => now(),
                'metadata' => json_encode($event->metadata)  // 👈 AGREGADO!
            ]);

            Log::info('✅ Notificación registrada', [
                'idpersona_facha_homologacion' => $event->idpersona_facha_homologacion
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error al registrar notificación', [
                'error' => $e->getMessage(),
                'idpersona_facha_homologacion' => $event->idpersona_facha_homologacion
            ]);
        }
    }
}
