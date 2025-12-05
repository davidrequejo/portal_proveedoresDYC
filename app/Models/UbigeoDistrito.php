<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UbigeoDistrito extends Model
{
    //use HasFactory;

    protected $table = 'ubigeo_distrito';

    protected $fillable = [
        'idubigeo_distrito', 'idubigeo_provincia', 'idubigeo_departamento', 'nombre', 
        'codigo_postal', 'ubigeo_reniec', 'ubigeo_inei', 'superficie', 'altitud', 
        'latitud', 'longitud', 'frontera', 'estado'
    ];

    // Método para obtener los distritos con sus respectivas provincias y departamentos
    public static function obtenerDistritos()
    {
        return DB::table('ubigeo_distrito as d')
            ->join('ubigeo_provincia as p', 'd.idubigeo_provincia', '=', 'p.idubigeo_provincia')
            ->join('ubigeo_departamento as dep', 'p.idubigeo_departamento', '=', 'dep.idubigeo_departamento')
            ->select('d.idubigeo_distrito', 'd.nombre as distrito', 'd.codigo_postal', 'p.nombre as provincia', 'dep.nombre as departamento')
            ->get();
    }
}
