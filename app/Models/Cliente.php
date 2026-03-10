<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{

    protected $table = 'persona';
    protected $primaryKey = 'idpersona';
    // Define los campos que son asignables
    // Define los campos que son asignables
    protected $fillable = [


        /* ================== CLAVES ================== */
        'idtipo_persona',
        'idtipoestandarproveedor',

        /* ================== CÓDIGOS ================== */
        'codigo_s10',

        /* ================== SUNAT ================== */
        'tipo_entidad_sunat',
        'tipo_documento',
        'numero_documento',

        /* ================== RAZÓN SOCIAL / COMERCIAL ================== */
        'nombre_razonsocial',
        'apellidos_nombrecomercial',

        /* ================== PERSONA NATURAL ================== */
        'nombre_persona_natural',
        'apellido_paterno_per_natural',
        'apellido_materno_per_natural',
        'sexo',
        'fecha_nacimiento',
        'tratamiento_pers_natural',
        'ruc_persona_natural',

        /* ================== REPRESENTANTE LEGAL ================== */
        'nombre_apellidos_representante_legal',
        'numerotelefo_representante_legal',

        /* ================== CONTACTO COMERCIAL ================== */
        'nombres_contacto_comercial',
        'cargo_contacto_comercial',
        'telefono_contacto_comercial',
        'correo_contacto_comercial',

        /* ================== CONTACTO GENERAL ================== */
        'celular',
        'email',

        /* ================== DIRECCIÓN ================== */
        'direccion',
        'direccion_referencia',
        'departamento',
        'provincia',
        'distrito',
        'cod_ubigeo',

        /* ================== PERIODO ================== */
        'fecha_inicio_periodo',
        'fecha_fin_periodo',

        /* ================== CUENTAS ================== */
        'cuenta_bancaria',
        'cci',
        'titular_cuenta',

        /* ================== SISTEMA ================== */
        'foto_perfil',
        'estado_completoxproveedor',
        'estado',
        'estado_delete',

        /* ================== AUDITORÍA ================== */
        'user_created',
        'user_updated',
        'user_delete',
        'user_trash',
    ];


    public static function obtenerListacliente()
    {
        return DB::table('persona as p')
            ->join('tipo_persona as tp', 'p.idtipo_persona', '=', 'tp.idtipo_persona')
            ->join('sunat_c06_doc_identidad as doc', 'p.tipo_documento', '=', 'doc.code_sunat')
            ->select('p.idpersona', 'tp.descripcion as tipoPersona', 'p.tipo_documento', 'p.nombre_razonsocial',
                'p.apellidos_nombrecomercial', 'doc.abreviatura', 'p.numero_documento', 'p.celular',
                'p.direccion', 'p.distrito', 'p.provincia', 'p.departamento', 'p.email' )
            ->where('p.estado', '1')
            ->where('p.estado_delete', '1')
            ->get();
    }





    


}



