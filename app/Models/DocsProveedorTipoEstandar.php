<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocsProveedorTipoEstandar extends Model
{
    // Nombre exacto de la tabla
    protected $table = 'docsproveedortipoestandar';

    // PK (porque no es "id")
    protected $primaryKey = 'iddocsproveedortipoestandar';

    public $timestamps = false;

    // Campos asignables (los que mostraste)
    protected $fillable = [
        'idpersona',
        'iddetalletipoestandarproveedor',
        'nombreDocumento',
        'archivo',
        'estado_revision',
        'observacion',
        'estado_trash',
        'estado_delete',
    ];
}
