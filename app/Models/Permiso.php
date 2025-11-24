<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permiso';
    protected $primaryKey = 'idpermiso';
    public $timestamps = true;

    protected $fillable = ['grupo','escenario','icono'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'usuario_permiso', 'idpermiso', 'idusuario')
                    ->withPivot('idusuario_permiso')
                    ->withTimestamps();
    }
}
