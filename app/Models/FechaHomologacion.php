<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FechaHomologacion  extends Model
{
    // Nombre de la tabla
    protected $table = 'fecha_homologacion';

    // Clave primaria
    protected $primaryKey = 'idfecha_homologacion';

    // Indica si la PK es autoincremental
    public $incrementing = true;

    // Tipo de la clave primaria
    protected $keyType = 'int';

    // Habilitar timestamps (created_at, updated_at)
    public $timestamps = true;

    // Campos asignables masivamente
    protected $fillable = [
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado_trash',
        'estado_delete',
        'user_trash',
        'user_delete',
        'user_created',
        'user_updated',
    ];


    // Método para obtener los select2
    public static function select2Homologacion()
    {
        return DB::table('fecha_homologacion as fh')
            ->select('fh.idfecha_homologacion', 'fh.descripcion','fh.fecha_inicio', 'fh.fecha_fin')
            ->get();
    }




}
