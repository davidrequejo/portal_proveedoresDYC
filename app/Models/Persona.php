<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{

    
    //public $timestamps = false; // si tu tabla no usa created_at / updated_at

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
        'fecha_nacimiento',

        /* ================== REPRESENTANTE LEGAL ================== */
        'nombre_apellidos_representante_legal',
        'numerodoc_representante_legal',

        /* ================== CONTACTO ================== */
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

        /* ================== CUENTAS BANCARIAS ================== */
        'cuenta_bancaria',
        'cci',
        'titular_cuenta',

        /* ================== SISTEMA ================== */
        'foto_perfil',
        'estado',
        'estado_delete',

        /* ================== AUDITORÍA ================== */
        'user_created',
        'user_updated',
        'user_delete',
        'user_trash',
    ];

     public function cuentasBancarias()
    {
        return $this->hasMany(
            PersonaCuentaBancaria::class,
            'idpersona',
            'idpersona'
        );
    }


    public static function personas()
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



