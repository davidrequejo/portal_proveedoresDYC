<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Logbd  extends Model
{
    // Nombre de la tabla
    protected $table = 'logbd';

    // Clave primaria
    protected $primaryKey = 'idlogbd';

    // Usar timestamps (created_at, updated_at)
    public $timestamps = true;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre_tabla',
        'id_registrotabla',
        'id_user',
        'idpersona',
        'observacion',
        'accion_realizada',
        'estado_sincronizacion10',
        'estado_trash',
        'estado_delete',
        'user_trash',
        'user_delete',
        'user_created',
        'user_updated',
    ];

}
