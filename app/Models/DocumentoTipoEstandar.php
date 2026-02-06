<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentoTipoEstandar extends Model
{
    protected $table = 'documento_tipo_estandar';
    protected $primaryKey = 'iddocumento_tipo_estandar';

    // Usa created_at y updated_at
    public $timestamps = true;

    protected $fillable = [
        'descripcion',
        'tipo_documento',
        'archivo',
        'estado_trash',
        'estado_delete',
        'user_trash',
        'user_delete',
        'user_created',
        'user_updated',
    ];

    /**
     * Scope: registros activos (no eliminados)
     */
    public function scopeActivos($query)
    {
        return $query->where('estado_delete', '1')
                     ->where('estado_trash', '1');
    }

    /**
     * Scope: solo eliminados (papelera)
     */
    public function scopeEliminados($query)
    {
        return $query->where('estado_trash', '0');
    }

    /**
     * Método para Select2 / combos
     */
    public static function select2DocumentoTipoEstandar()
    {
        return DB::table('documento_tipo_estandar as d')
            ->select(
                'd.iddocumento_tipo_estandar',
                'd.descripcion','d.tipo_documento'
            )
            ->where('d.estado_trash', '1')
            ->where('d.estado_delete', '1')
            ->orderBy('d.descripcion')
            ->get();
    }
}
