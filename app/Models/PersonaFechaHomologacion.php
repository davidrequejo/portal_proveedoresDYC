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
        return DB::table('idpersona_facha_homologacion as pfh')
            ->select(
                'pfh.idpersona_facha_homologacion',
                'pfh.descripcion'
            )
            ->where('pfh.estado_trash', '1')
            ->where('pfh.estado_delete', '1')
            ->orderBy('pfh.descripcion')
            ->get();
    }

    /**
     * Relación con Persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idpersona');
    }

    /**
     * Relación con FechaHomologacion
     */
    public function fechaHomologacion()
    {
        return $this->belongsTo(FechaHomologacion::class, 'idfecha_homologacion');
    }
}
