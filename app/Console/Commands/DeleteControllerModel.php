<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeleteControllerModel extends Command
{
    /**
     * Nombre y firma del comando.
     *
     * Ejemplo de uso:
     * php artisan delete:cm Nombre
     */
    protected $signature = 'delete:cm {name}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Elimina un Modelo y su Controlador asociado';

    /**
     * Ejecutar el comando.
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Rutas de archivos
        $modelPath = app_path("Models/{$name}.php");
        $controllerPath = app_path("Http/Controllers/{$name}Controller.php");

        // Eliminar Modelo
        if (File::exists($modelPath)) {
            File::delete($modelPath);
            $this->info("✅ Modelo eliminado: {$modelPath}");
        } else {
            $this->warn("⚠️ No se encontró el modelo: {$modelPath}");
        }

        // Eliminar Controlador
        if (File::exists($controllerPath)) {
            File::delete($controllerPath);
            $this->info("✅ Controlador eliminado: {$controllerPath}");
        } else {
            $this->warn("⚠️ No se encontró el controlador: {$controllerPath}");
        }

        $this->info("✨ Proceso terminado.");
    }
}
