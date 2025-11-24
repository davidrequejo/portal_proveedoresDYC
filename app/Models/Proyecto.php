<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyecto';
    protected $primaryKey = 'idproyecto';
    public $timestamps = false;

    protected $fillable = [
        'codigo','descripcion','empresa','cliente','direccion','ubicacion',
        'total_presupuesto','fecha_inicio','fecha_fin'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'total_presupuesto' => 'decimal:2',
    ];

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class, 'idproyecto', 'idproyecto');
    }
}