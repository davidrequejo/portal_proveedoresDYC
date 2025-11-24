<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoElemento extends Model
{
    protected $table = 'tipoelemento';
    protected $primaryKey = 'idtipoelemento';
    public $timestamps = false;

    protected $fillable = ['codigo', 'descripcion'];

    // 1 tipo -> muchos recursos para la relacions
    public function recursos()
    {
        return $this->hasMany(Recurso::class, 'idtipoelemento', 'idtipoelemento');
    }
}
