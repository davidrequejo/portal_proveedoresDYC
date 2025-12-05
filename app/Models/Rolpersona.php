<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rolpersona extends Model
{
    //use HasFactory;

    protected $table = 'tipo_persona';

    protected $fillable = [
        'idtipo_persona', 'descripcion',  'estado_trash'
    ];

    // Método para obtener los distritos con sus respectivas provincias y departamentos
    public static function select2rolpersona()
    {
        return DB::table('tipo_persona as tp')
            ->select('tp.idtipo_persona', 'tp.descripcion')
            ->get();
    }
}
