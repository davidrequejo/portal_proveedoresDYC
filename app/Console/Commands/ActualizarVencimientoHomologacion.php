<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActualizarVencimientoHomologacion extends Command
{
    /**
     * El nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'homologacion:actualizar-vencidos';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de homologaciones vencidas a "Vencido"';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        $hoy = Carbon::today(); // Fecha actual sin hora (00:00:00)

        // Buscar registros donde fecha_fin_periodo_h sea menor a hoy
        // y el estado no sea ya 'Vencido'
        $actualizados = DB::table('persona_facha_homologacion')
            ->whereDate('fecha_fin_periodo_h', '<', $hoy) // Solo comparamos fecha (sin hora)
            ->where('estado_homologacion', '!=', 'Vencido') // Ajusta el valor exacto de tu estado
            // O también podrías usar: ->whereNull('estado_homologacion') si el estado puede ser nulo
            ->update(['estado_homologacion' => 'Vencido']);

        // Mostrar mensaje en consola
        $this->info("Se actualizaron {$actualizados} registros a estado Vencido.");
    }
}