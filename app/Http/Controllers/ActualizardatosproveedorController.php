<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Proveedor;


class ActualizardatosproveedorController extends Controller
{
    public function index()
    {
       return view('actualizardatos');
    }

    public function ver_proveedorupdate($idpersona)
    {
        try {

            // 1. Buscar proveedor por idpersona
            $proveedor = Proveedor::where('idpersona', $idpersona)->firstOrFail();

            return ApiResponse::success([ 'proveedor' => $proveedor, ], 'Proveedor encontrado');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editarProveedor(Request $request)
    {
        $proveedor = Proveedor::findOrFail($request->idpersonaUpdate);

        $data = [

            /* ================== SUNAT ================== */
            'tipo_entidad_sunat' => $request->tipo_entidad_sunat,
            'tipo_documento'     => $request->tipo_documento_input1,
            'numero_documento'   => $request->numero_documento_input1,

            /* ================== RAZÓN SOCIAL ================== */
            'nombre_razonsocial' => $request->nombre_razonsocial_input1,

            /* ================== PERSONA NATURAL ================== */
            'nombre_persona_natural'       => $request->nombre_persona_natural,
            'apellido_paterno_per_natural' => $request->apellido_paterno_per_natural,
            'apellido_materno_per_natural' => $request->apellido_materno_per_natural,
            'sexo'              => $request->sexo,
            'fecha_nacimiento'  => $request->fecha_nacimiento,
            'ruc_persona_natural'       => $request->ruc_pers_nat,
            'tratamiento_pers_natural'  => $request->tratamiento_pers_nat,

            /* ================== REPRESENTANTE LEGAL ================== */
            'nombre_apellidos_representante_legal' => $request->nombre_apellidos_representante_legal,
            'numerotelefo_representante_legal'     => $request->telefono_representante,

            /* ================== CONTACTO COMERCIAL ================== */
            'nombres_contacto_comercial' => $request->nombre_apellidos_contacto_comercial,
            'cargo_contacto_comercial'   => $request->cargo_contacto_comercial,
            'telefono_contacto_comercial'=> $request->telefono_contacto_comercial,
            'correo_contacto_comercial'  => $request->email_contacto_comercial,

            /* ================== CONTACTO GENERAL ================== */
            'celular' => $request->celular,
            'email'   => $request->email,

            /* ================== DIRECCIÓN ================== */
            'direccion'    => $request->direccion,
            'departamento' => $request->departamento,
            'provincia'    => $request->provincia,
            'distrito'     => $request->distrito,

            /* ================== AUDITORÍA ================== */
            'user_updated' => auth()->id(),
        ];

        // Evita sobrescribir con NULL
        $data = array_filter($data, fn ($v) => $v !== null);

        $proveedor->update($data);


        return ApiResponse::success([
            'e' =>'Registro actualizado',
        ], 'Proveedor actualizado correctamente');
    }

    
}
