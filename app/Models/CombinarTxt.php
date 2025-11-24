<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CombinarTxt extends Model
{
    protected $table = 'archivos_data';
    protected $fillable = [
        'codalterno', 'dni', 'codbase', 'monto1', 'monto2', 'monto3', 'nombre_archivo'
    ];
    public $timestamps = false;
}
