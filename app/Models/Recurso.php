<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    protected $table = 'recurso';
    protected $primaryKey = 'idrecurso';
    public $timestamps = false;

    protected $fillable = [
        'idtipoelemento','codigo','descripcion','nivel'
    ];

    // N recursos -> pertenece a 1 tipo
    public function tipoElemento()
    {
        return $this->belongsTo(TipoElemento::class, 'idtipoelemento', 'idtipoelemento');
    }
}
