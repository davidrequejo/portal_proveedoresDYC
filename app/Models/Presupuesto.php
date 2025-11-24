<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    protected $table = 'presupuesto';
    protected $primaryKey = 'idpresupuesto';
    public $timestamps = true;

    protected $fillable = [
        'idproyecto','idpresupuesto_grupo','fecha','tipo','icono','icono_color','estado_trash','estado_delete',
        'user_trash','user_delete','user_created','user_updated','descripcion','descripcion_resumen'
    ];

    protected $casts = ['fecha' => 'date'];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'idproyecto', 'idproyecto');
    }

    public function grupo()
    {
        return $this->belongsTo(PresupuestoGrupo::class, 'idpresupuesto_grupo', 'idpresupuesto_grupo');
    }

    public function detalles()
    {
        return $this->hasMany(PresupuestoDetalle::class, 'idpresupuesto', 'idpresupuesto');
    }
}
