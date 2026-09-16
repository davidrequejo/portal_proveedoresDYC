<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Cliente;
use App\Models\Logbd;
use Illuminate\Support\Facades\Validator;
use App\Mail\ProveedorActualizadoLogisticaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Traits\RegistraLogCompleto;


class ActualizardatosclienteController extends Controller
{
    use RegistraLogCompleto;

    public function getConfigLog($tabla)
    {
        $configs = [
            'persona' => [
                'labels' => [
                    'nombre_razonsocial' => 'Razon Social',
                    'nombre_persona_natural' => 'Nombres',
                    'apellido_paterno_per_natural' => 'Apellido Paterno',
                    'apellido_materno_per_natural' => 'Apellido Materno',
                    'sexo' => 'Sexo',
                    'fecha_nacimiento' => 'Fecha Nacimiento',
                    'tipo_documento' => 'Tipo Documento',
                    'numero_documento' => 'Nro Documento',
                    'celular' => 'Celular',
                    'email' => 'Correo',
                    'direccion' => 'Direccion',
                    'departamento' => 'Departamento',
                    'provincia' => 'Provincia',
                    'distrito' => 'Distrito',
                    'tipo_entidad_sunat' => 'Tipo Persona',
                    'codigo_s10' => 'Codigo S10',
                    'ruc_persona_natural' => 'DNI Persona Natural',
                    'tratamiento_pers_natural' => 'Tratamiento',
                    'nombre_apellidos_representante_legal' => 'Representante Legal',
                    'numerotelefo_representante_legal' => 'Telefono Representante',
                    'nombres_contacto_comercial' => 'Contacto Comercial',
                    'cargo_contacto_comercial' => 'Cargo Contacto Comercial',
                    'telefono_contacto_comercial' => 'Telefono Contacto Comercial',
                    'correo_contacto_comercial' => 'Correo Contacto Comercial',
                ],
                'formatters' => [
                    'sexo' => 'sexo',
                    'fecha_nacimiento' => 'fecha',
                    'celular' => 'celular',
                    'numero_documento' => 'documento',
                    'ruc_persona_natural' => 'documento',
                    'email' => 'email',
                    'correo_contacto_comercial' => 'email',
                    'tipo_documento' => 'tipo_documento',
                ],
                'ignorar' => ['updated_at', 'user_updated', 'created_at', 'user_created']
            ],
        ];

        return $configs[$tabla] ?? [
            'labels' => [],
            'formatters' => [],
            'ignorar' => ['updated_at', 'user_updated', 'created_at', 'user_created']
        ];
    }

    public function index()
    {
       return view('actualizardatoscliente');
    }

    public function ver_clienteupdate($idpersona)
    {
        try {

            // 1. Buscar cliente por idpersona
            $cliente = Cliente::where('idpersona', $idpersona)->firstOrFail();

            return ApiResponse::success([ 'cliente' => $cliente, ], 'Cliente encontrado');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editarcliente(Request $request)
    {
       $cliente = Cliente::findOrFail($request->idpersonaUpdate);
       $dniPersonaNatural = $request->ruc_pers_nat;

       if ($request->tipo_entidad_sunat === 'NATURAL') {
            if ($request->tipo_documento_input1 === '1') {
                $dniPersonaNatural = $request->numero_documento_input1;
            } elseif (
                empty($dniPersonaNatural)
                && $request->tipo_documento_input1 === '6'
                && strlen((string) $request->numero_documento_input1) === 11
            ) {
                $dniPersonaNatural = substr((string) $request->numero_documento_input1, 2, -1);
            }
       }

       $request->merge(['ruc_pers_nat' => $dniPersonaNatural]);

        /* ================== VALIDACIÓN BASE ================== */
        $rules = [
            'tipo_entidad_sunat' => ['required'],
        ];

        /* ================== PERSONA NATURAL ================== */
        if ($request->tipo_entidad_sunat === 'NATURAL') {
            $rules = array_merge($rules, [
                'tipo_documento_input1'        => ['required'],
                'numero_documento_input1'      => ['required'],
                'nombre_razonsocial_input1'    => ['required'],
                'nombre_persona_natural'       => ['required'],
                'apellido_paterno_per_natural' => ['required'],
                'apellido_materno_per_natural' => ['required'],
                'sexo'                         => ['required'],
                'fecha_nacimiento'             => ['required', 'date'],
                'ruc_pers_nat'                 => ['nullable', 'digits:8'],
                'tratamiento_pers_nat'         => ['required'],
                'celular'                      => ['required'],
                'email'                        => ['required', 'email'],
                'direccion'                    => ['required'],
                'departamento'                 => ['required'],
                'provincia'                    => ['required'],
                'distrito'                     => ['required'],
            ]);
        }

        /* ================== PERSONA JURÍDICA ================== */
        if ($request->tipo_entidad_sunat === 'JURIDICA') {
            $rules = array_merge($rules, [
                'tipo_documento_input1'                   => ['required'],
                'numero_documento_input1'                 => ['required'],
                'nombre_razonsocial_input1'               => ['required'],
                'nombre_apellidos_representante_legal'    => ['required'],
                'telefono_representante'                  => ['required'],
                'nombre_apellidos_contacto_comercial'     => ['required'],
                'cargo_contacto_comercial'                => ['required'],
                'telefono_contacto_comercial'             => ['required'],
                'email_contacto_comercial'                => ['required', 'email'],
                'celular'                                 => ['required'],
                'email'                                   => ['required', 'email'],
                'direccion'                               => ['required'],
                'departamento'                            => ['required'],
                'provincia'                               => ['required'],
                'distrito'                                => ['required'],
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ApiResponse::validation(
                $validator->errors()->toArray(),
                'Campos Por Rellenar Correctamente'
            );
        }

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
        // Actualizar cliente
        $cliente->update($data);

        $primerLog = Logbd::where('nombre_tabla', 'persona')
            ->where('id_registrotabla', $cliente->idpersona)
            ->doesntExist();

        if ($primerLog) {
            $this->registrarSnapshot(
                $cliente,
                'persona',
                $cliente->idpersona,
                'REGISTRO_INICIAL_CLIENTE'
            );
        } else {
            $cambios = $cliente->getChanges();
            $this->registrarCambios(
                $cliente,
                'persona',
                $cliente->idpersona,
                $cambios,
                'ACTUALIZAR'
            );
        }


        /** Enviar correo de notificación a logística */
        $logistica = DB::table('persona')
        ->join('tipo_persona', 'persona.idtipo_persona', '=', 'tipo_persona.idtipo_persona')
        ->where('persona.idtipo_persona', 6)
        ->where('persona.estado', 1)
        ->where('persona.estado_delete', 1)
        ->select('persona.idpersona', 'persona.nombre_razonsocial', 'persona.email', 'tipo_persona.descripcion')
        ->get();

        foreach ($logistica as $usuarioLogistica) {
            // Definir tipo y acción basados en el contexto de la actualización
            $tipo = 'cliente';  // O 'cuenta_bancaria' o 'cliente'

            // Enviar el correo con los datos adecuados
            Mail::to($usuarioLogistica->email)->queue(new ProveedorActualizadoLogisticaMail($cliente, $tipo));
        }



        return ApiResponse::success([
            'e' =>'Registro actualizado',
        ], 'Cliente actualizado correctamente');
    }

    
}
