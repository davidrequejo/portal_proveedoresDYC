<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_estandar extends Model
{
    protected $table = 'tipoestandarproveedor';
    protected $primaryKey = 'idtipoestandarproveedor';
    //public $timestamps = true;

    protected $fillable = ['descripcion', 'nroDocumentos','estado_trash'];

    public function detalles()
    {
        return $this->hasMany(
            Tipo_estandarDetalle::class,
            'idtipoestandarproveedor',    // FK correcta
            'idtipoestandarproveedor'     // PK correcta
        );
    }

        // Método para obtener los distritos con sus respectivas provincias y departamentos
    public static function select2tipoestandar()
    {
        return DB::table('tipoestandarproveedor as te')
            ->select('te.idtipoestandarproveedor', 'te.descripcion')
            ->get();
    }




}
