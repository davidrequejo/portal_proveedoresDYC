<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportarHoraOberti extends Model
{
    protected $table = 'registro_horas';
    protected $primaryKey = 'idregistro_horas';
    public $timestamps = false; // solo tienes created_at

    protected $fillable = [
        'nombre_archivo',
        'mime_type',
        'file_size',
        'sheet_index',
        'sheet_name',
        'total_filas','filas_importadas',        
        'created_at'      
       
    ];

    protected $casts = [        
        'created_at'     => 'datetime',
    ];

    public function detalles()
    {
        return $this->hasMany(ImportarHoraDetalleOberti::class, 'idregistro_horas', 'idregistro_horas');
    }
}
