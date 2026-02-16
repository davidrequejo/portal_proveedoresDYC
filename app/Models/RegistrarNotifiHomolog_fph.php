<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrarNotifiHomolog_fph extends Model
{
    protected $table = 'correnotificacion_f_h';
    //protected $primaryKey = 'idcorrenotificacion_f_h';
    //protected $primaryKey = 'idcorrenotificacion_f_h';
    
    protected $fillable = [
        'idpersona_facha_homologacion',
        'estado',
        'fecha_envio',
        'metadata'
    ];


    public function PersonaFechaHomologacion()
    {
        return $this->belongsTo(PersonaFechaHomologacion::class, 'idpersona_facha_homologacion');

    }
}