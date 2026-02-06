<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Areapersona extends Model
{
    //use HasFactory;

    protected $table = 'area_persona';

    protected $fillable = [
        'ididarea_persona', 'descripcion',  'estado_trash'
    ];

    // Método para obtener los distritos con sus respectivas provincias y departamentos
    public static function select2areapersona()
    {
        return DB::table('area_persona as ap')
            ->select('ap.idarea_persona', 'ap.descripcion')
            ->get();
    }
}
