<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PersonaFechaHomologacion extends Model
{
    protected $table = 'persona_facha_homologacion';
    protected $primaryKey = 'idpersona_facha_homologacion';

    // timestamps: created_at y updated_at
    public $timestamps = true;

    protected $fillable = [
        'idpersona',
        'idfecha_homologacion',
        'descripcion',
        'estado_trash',
        'estado_delete',
        'user_trash',
        'user_delete',
        'estado_trash_copy1',
        'user_updated',
    ];

    /**
     * Scope: registros activos (no eliminados)
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_delete', '1');
    }

    /**
     * Scope: registros visibles (no enviados a trash)
     */
    public function scopeNoTrash($query)
    {
        return $query->where('estado_trash', '1');
    }

    /**
     * Método para combos / Select2
     */
    public static function select2PersonaFechaHomologacion()
    {
        return DB::table('persona_facha_homologacion as pfh')
            ->select(
                'pfh.idpersona_facha_homologacion',
                'pfh.descripcion'
            )
            ->where('pfh.estado_trash', '1')
            ->where('pfh.estado_delete', '1')
            ->orderBy('pfh.descripcion')
            ->get();
    }


    public static function select2estado_homologacion()
    {
        return  DB::table('persona_facha_homologacion as pfh')
              ->select('pfh.estado_homologacion')
              ->where('pfh.estado_trash', '1')
              ->where('pfh.estado_delete', '1')
              ->distinct()
              ->orderBy('pfh.estado_homologacion')
              ->get();
    }


    
    public static function select2proveedor()
    {
        return  DB::table('persona_facha_homologacion as pfh')
              ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
              ->join('sunat_c06_doc_identidad as sd', 'p.tipo_documento', '=', 'sd.code_sunat')
              ->select('p.idpersona as idpersona', 'p.nombre_razonsocial', 'p.numero_documento', 'sd.abreviatura as tipodocumento' )
              ->where('pfh.estado_trash', '1')
              ->where('pfh.estado_delete', '1')
              ->groupBy('p.idpersona', 'p.nombre_razonsocial', 'p.numero_documento', 'sd.abreviatura' )
              ->orderBy('p.nombre_razonsocial')
              ->get();
    }

    //no se esta usando, se cambio por select2compradores
    public static function select2usuarioproceso()
    {
        return DB::table('persona_facha_homologacion as pfh')
            ->join('users as u', 'u.id', '=', 'pfh.user_init_process')
            ->join('persona as p', 'u.idpersona', '=', 'p.idpersona')
            ->join('sunat_c06_doc_identidad as sd', 'p.tipo_documento', '=', 'sd.code_sunat')
            ->select( 'u.id as iduser', 'p.idpersona as idpersona', 'p.nombre_razonsocial', 'p.numero_documento', 'sd.abreviatura as tipodocumento' )
            ->where('pfh.estado_trash', '1')
            ->where('pfh.estado_delete', '1')
            ->groupBy('u.id', 'p.idpersona', 'p.nombre_razonsocial', 'p.numero_documento', 'sd.abreviatura' )
            ->orderBy('p.nombre_razonsocial')
            ->get();
    }


    public static function select2compradores()
    {
        return DB::table('persona_facha_homologacion as pfh')
            ->join('persona as p', 'pfh.idpersonacomprador', '=', 'p.idpersona')
            ->join('sunat_c06_doc_identidad as sd', 'p.tipo_documento', '=', 'sd.code_sunat')
            ->select( 'p.idpersona as idpersona', 'p.nombre_razonsocial', 'p.numero_documento', 'sd.abreviatura as tipodocumento' )
            ->where('pfh.estado_trash', '1')
            ->where('pfh.estado_delete', '1')
            ->groupBy('p.idpersona', 'p.nombre_razonsocial', 'p.numero_documento', 'sd.abreviatura' )
            ->orderBy('p.nombre_razonsocial')
            ->get();
    }

    /**
     * Relación con Persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idpersona');
    }
    
    // Método para obtener homologaciones vigentes con datos de persona
    public static function seleccion_evaluacion()
    {
        return DB::table('persona_facha_homologacion as pfh')
            ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
            ->select( 'pfh.idpersona_facha_homologacion', 'pfh.idpersona', 'pfh.fecha_inicio_periodo_h', 'pfh.fecha_fin_periodo_h', 'pfh.estado_homologacion', 'p.nombre_razonsocial', 'p.numero_documento' )
            ->where('pfh.estado_homologacion', 'Vigente')
            ->where('pfh.estado_trash', '1')
            ->where('pfh.estado_delete', '1')
            ->orderBy('p.nombre_razonsocial')
            ->get();

    }


}
