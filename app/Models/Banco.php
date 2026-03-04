<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Banco extends Model
{
    protected $table = 'banco';
    protected $primaryKey = 'idbanco';

    // Laravel maneja automáticamente created_at y updated_at
    public $timestamps = true;

    protected $fillable = [
        'codigo_bank_s10',
        'descripcion',
        'abreviatura',
        'estado_trash',
        'estado_delete',
        'user_trash',
        'user_delete',
        'user_created',
        'user_updated',
    ];

    /**
     * Scope para registros activos
     */
    public function bancosActivos($query)
    {
        return $query->where('estado_delete', '1');
    }

    /**
     * Método para Select2 u otros combos
     */
    public static function select2Bancos()
    {
        return DB::table('banco as b')
            ->select('b.idbanco', 'b.descripcion')
            ->where('b.estado_trash', '1')
            ->orderBy('b.descripcion')
            ->get();
    }

        /**
     * Obtiene el código de Reniec para un distrito dado.
     * @param int $idDistrito El ID del distrito
     * @return string|null El código de Reniec o null si no se encuentra
     */
    public static function getCodigos10Idbn($idb)
    {
        if (!$idb) {
            return null;
        }

        return self::where('idbanco', $idb)
                    ->value('codigo_bank_s10'); // o 'codigo_bank_s10' según tu campo
    }

    public static function getabreviaturabn($idb)
    {
        if (!$idb) {
            return null;
        }

        return self::where('idbanco', $idb)
                    ->value('abreviatura'); // o 'abreviatura' según tu campo
    }


}
