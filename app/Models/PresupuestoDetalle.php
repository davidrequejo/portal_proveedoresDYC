<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresupuestoDetalle extends Model
{
    protected $table = 'presupuesto_detalle';
    protected $primaryKey = 'idpresudetalle';
    public $timestamps = false;

    protected $fillable = [
        'idpresupuesto','codigo','codigo_alterno','descripcion',
        'cantidad','und','precio','monto','nivel','tipo','id_recurso'
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'precio'   => 'decimal:2',
        'monto'    => 'decimal:2',
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'idpresupuesto', 'idpresupuesto');
    }
}