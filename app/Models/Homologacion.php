<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Homologacion  extends Model
{
    // Nombre de la tabla
    protected $table = 'persona_facha_homologacion';

    // Clave primaria
    protected $primaryKey = 'idpersona_facha_homologacion';

    // Indica si la PK es autoincremental
    public $incrementing = true;

    // Tipo de la clave primaria
    protected $keyType = 'int';

    // Habilitar timestamps (created_at, updated_at)
    public $timestamps = true;

    // Campos asignables masivamente
    protected $fillable = [
        'idpersona',
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
        return DB::table('persona_facha_homologacion as fh')
            ->select('fh.idpersona_facha_homologacion', 'fh.descripcion','fh.fecha_inicio', 'fh.fecha_fin')
            ->get();
    }




}
