<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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


    //metodo para mostrar los registros de la tabla persona con el tipo persona comprador y administrador
    public static function select2pers_compr_adm()
    {
        return DB::table('persona as p')
            ->join('tipo_persona as ptp', 'p.idtipo_persona', '=', 'ptp.idtipo_persona')
            ->select( 'p.idpersona as idpersona', 'p.nombre_razonsocial', 'p.numero_documento')
            ->whereIn('ptp.idtipo_persona', [2, 6]) // Comprador o Administrador
            ->wherenotin('p.idpersona', [11,70,71]) // Excluir personas con idpersona 1 y 2
            ->where('p.estado', '1')
            ->where('p.estado_delete', '1')
            ->orderBy('p.nombre_razonsocial')
            ->get();
    }




}
