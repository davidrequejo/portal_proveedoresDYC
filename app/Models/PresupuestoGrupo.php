<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoGrupo extends Model
{
    protected $table = 'presupuesto_grupo';
    protected $primaryKey = 'idpresupuesto_grupo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true; // usa created_at / updated_at

    protected $fillable = ['descripcion', 'icono', 'icono_color',];

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class, 'idpresupuesto_grupo', 'idpresupuesto_grupo');
    }
}
