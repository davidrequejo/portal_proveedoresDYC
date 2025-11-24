<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Collections\RowCollection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CombinarExcelAfp extends Model
{
    

    protected $table = 'planilla_excel_afp';
    public $timestamps = false;

    protected $fillable = [
        'n_secuencia','cuspp','tipo_documento','n_documento',
        'apellido_paterno','apellido_materno','nombres',
        'relacion_laboral','inicio_relacion_laboral','cese_relacion_laboral',
        'excepcion_de_aportar','rem_asegurable',
        'aporte_con_fin','aporte_sin_fin','aporte_empleador',
        'rubro_trabajador','afp',
        'archivo_nombre', 'created_at',
    ];

   
    public function headingRow(): int
    {
        return 1; // la primera fila es encabezado
    }

    public function chunkSize(): int
    {
        return 1000; // seguro para 500+ filas
    }

    
}
