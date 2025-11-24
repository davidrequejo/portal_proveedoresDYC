<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportarHoraDetalleOberti extends Model
{
    protected $table = 'registro_horas_detalle';
    protected $primaryKey = 'idregistro_horas_detalle';
    public $timestamps = false;

    protected $fillable = [
        'idregistro_horas',
        'periodo_inicio','periodo_fin','nro_hoja','apellidos_nombres','dni','cargo','fecha_ingreso','hijos',
        // Hora por celda (HN/HE) exactos
        'lunes_hn','lunes_he',
        'martes_hn','martes_he',
        'miercoles_hn','miercoles_he',
        'jueves_hn','jueves_he',
        'viernes_hn','viernes_he',
        'sabado_hn','sabado_he',
        'domingo_hn','domingo_he',
        // colores por celda (HN/HE) exactos
        'lunes_hn_bg_color','lunes_he_bg_color',
        'martes_hn_bg_color','martes_he_bg_color',
        'miercoles_hn_bg_color','miercoles_he_bg_color',
        'jueves_hn_bg_color','jueves_he_bg_color',
        'viernes_hn_bg_color','viernes_he_bg_color',
        'sabado_hn_bg_color','sabado_he_bg_color',
        'domingo_hn_bg_color','domingo_he_bg_color',
        // Colores de texto (nombre) por celda (HN/HE)
        'lunes_hn_nombre_color','lunes_he_nombre_color',
        'martes_hn_nombre_color','martes_he_nombre_color',
        'miercoles_hn_nombre_color','miercoles_he_nombre_color',
        'jueves_hn_nombre_color','jueves_he_nombre_color',
        'viernes_hn_nombre_color','viernes_he_nombre_color',
        'sabado_hn_nombre_color','sabado_he_nombre_color',
        'domingo_hn_nombre_color','domingo_he_nombre_color',

        'total_dias_laborados','total_horas_extras','observaciones',
        'proyecto','partida_control','concepto',
        'created_at'
    ];

    protected $casts = [
        'periodo_inicio' => 'date',
        'periodo_fin'    => 'date',
        'fecha_ingreso'  => 'date',
        'created_at'     => 'datetime',
    ];

    public function cabecera()
    {
        return $this->belongsTo(ImportarHoraOberti::class, 'idregistro_horas', 'idregistro_horas');
    }
}
