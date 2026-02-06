<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Mail\VencimientoHomologacionMail;
use Illuminate\Support\Facades\Mail;

class VerificarVencimientoHomologacion extends Command
{
    protected $signature = 'homologacion:verificar-vencimiento';
    protected $description = 'Guarda notificaciones cuando faltan 15 y 5 días para vencer';

    public function handle()
    {
        $hoy = Carbon::today();

        $registros = DB::table('persona_facha_homologacion as pfh')
            ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
            ->select(
                'pfh.idpersona_facha_homologacion',
                'pfh.descripcion',
                'pfh.fecha_fin_periodo_h',
                'pfh.user_created',
                'pfh.notificado_15dias',
                'pfh.notificado_5dias',
                'p.nombre_razonsocial'
            )
            ->whereNotNull('pfh.fecha_fin_periodo_h')
            ->get();

        foreach ($registros as $r) {

            $diasRestantes = (int) $hoy->diffInDays( Carbon::parse($r->fecha_fin_periodo_h), false );

            if ($diasRestantes < 0) { continue;}

           /* dd([
    'hoy' => $hoy->toDateString(),
    'fecha_fin' => $r->fecha_fin_periodo_h,
    'diasRestantes' => $diasRestantes,
    'notificado_15dias' => $r->notificado_15dias,
    'notificado_5dias' => $r->notificado_5dias,
]);*/
            // ============================
            // 🧠 DECISIÓN ÚNICA
            // ============================
            if ($diasRestantes === 15 && $r->notificado_15dias == 0) {
                $mensaje = '⚠️ La homologación vence en 15 días';
                $campoFlag = 'notificado_15dias';
            } elseif ($diasRestantes === 5 && $r->notificado_5dias == 0) {
                $mensaje = '⛔ La homologación vence en 5 días';
                $campoFlag = 'notificado_5dias';
            } else {
                continue;
            }

            // ============================
            // 🔔 GUARDAR NOTIFICACIÓN
            // ============================
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\VencimientoHomologacionNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $r->user_created,
                'data' => json_encode([
                    'mensaje' => $mensaje,
                    'Proveedor' => $r->nombre_razonsocial ?? 'Persona',
                    'descripcion' => $r->descripcion ?? 'Homologación',
                    'fecha_fin' => $r->fecha_fin_periodo_h,
                    'dias_restantes' => $diasRestantes,
                    'tipo' => 'vencimiento'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ============================
            // 🏷️ MARCAR FLAG CORRECTO
            // ============================
            DB::table('persona_facha_homologacion')
                ->where('idpersona_facha_homologacion', $r->idpersona_facha_homologacion)
                ->update([$campoFlag => 1]);

            // ============================
            // 📧 CORREO
            // ============================
            $user = DB::table('users as u')
                ->join('persona as p', 'p.idpersona', '=', 'u.idpersona')
                ->select('p.nombre_razonsocial as nombre', 'p.email')
                ->where('u.id', $r->user_created)
                ->first();

            if (!$user || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            Mail::to($user->email, $user->nombre)
                ->queue(new VencimientoHomologacionMail([
                    'mensaje' => $mensaje,
                    'proveedor' => $r->nombre_razonsocial,
                    'descripcion' => $r->descripcion,
                    'fecha_fin' => $r->fecha_fin_periodo_h,
                    'dias_restantes' => $diasRestantes,
                    'nombre_usuario' => $user->nombre,
                ]));
        }

        $this->info('Notificaciones procesadas correctamente.');
    }

}
