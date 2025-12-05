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
        'idtipo_persona', 
        'idbanco', 
        'tipo_entidad_sunat', 
        'nombre_razonsocial', 
        'apellidos_nombrecomercial',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'celular',
        'direccion',
        'direccion_referencia',
        'departamento',
        'provincia',
        'distrito',
        'cod_ubigeo',
        'email',
        'cuenta_bancaria',
        'cci',
        'titular_cuenta',
        'foto_perfil',
        'estado',
    ];


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



