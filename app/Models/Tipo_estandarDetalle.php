<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_estandarDetalle extends Model
{
    protected $table = 'detalletipoestandarproveedor';

    protected $primaryKey = 'iddetalletipoestandarproveedor';

    public $timestamps = false;

    protected $fillable = [
        'idtipoestandarproveedor',  // FK correcta
        'iddocumento_tipo_estandar',  // FK correcta
        'detalle',
        'estado_trash'
    ];
}
