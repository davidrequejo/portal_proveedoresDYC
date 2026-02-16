<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Proveedor;
use App\Models\Logbd;
use Illuminate\Support\Facades\Validator;
use App\Mail\ProveedorActualizadoLogisticaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Traits\RegistraLogCompleto; // 👈 1. IMPORTAR


class ActualizardatosproveedorController extends Controller
{
    use RegistraLogCompleto;   // 👈 2. USAR EL TRAIT

    public function index()
    {
       return view('actualizardatos');
    }

    // 👇 3. IMPLEMENTAR EL MÉTODO OBLIGATORIO
    public function getConfigLog($tabla)
    {
        $configs = [
            // 🔵 TABLA PERSONA (PROVEEDORES)
            'persona' => [
                'labels' => [
                    'nombre_razonsocial' => 'Razón Social',
                    'nombre_persona_natural' => 'Nombres',
                    'apellido_paterno_per_natural' => 'Apellido Paterno',
                    'apellido_materno_per_natural' => 'Apellido Materno',
                    'sexo' => 'Sexo',
                    'fecha_nacimiento' => 'Fecha Nacimiento',
                    'ruc_persona_natural' => 'RUC',
                    'numero_documento' => 'N° Documento',
                    'celular' => 'Celular',
                    'email' => 'Correo',
                    'direccion' => 'Dirección',
                ],
                'formatters' => [
                    'sexo' => 'sexo',
                    'fecha_nacimiento' => 'fecha',
                    'celular' => 'celular',
                    'numero_documento' => 'documento',
                    'ruc_persona_natural' => 'documento',
                    'email' => 'email',
                ],
                'ignorar' => ['updated_at', 'user_updated', 'created_at', 'user_created']
            ],
            
            // 🟢 TABLA HOMOLOGACIÓN
            /*'homologacion' => [
                'labels' => [
                    'tipo_compra' => 'Tipo de Compra',
                    'inicio_proceso' => 'Inicio Proceso',
                    'fin_periodo' => 'Fin Período',
                    'estado' => 'Estado',
                    'descripcion' => 'Descripción',
                ],
                'formatters' => [
                    'inicio_proceso' => 'fecha',
                    'fin_periodo' => 'fecha_homologacion',
                    'estado' => 'estado_homologacion',
                ],
                'ignorar' => ['updated_at', 'user_updated']
            ],*/
            
            // 🟡 TABLA DOCUMENTOS
            /*'documentos' => [
                'labels' => [
                    'descripcion' => 'Documento',
                    'estado' => 'Estado',
                    'tamano' => 'Tamaño',
                    'tipo' => 'Tipo',
                ],
                'formatters' => [
                    'tamano' => 'tamano_archivo',
                    'estado' => 'estado_homologacion',
                    'tipo' => 'tipo_documento',
                ],
                'ignorar' => ['updated_at', 'user_updated']
            ],*/
            
            // 🟠 TABLA CUENTAS BANCARIAS
            /*'cuenta_bancaria' => [
                'labels' => [
                    'numero_cuenta' => 'N° Cuenta',
                    'banco' => 'Banco',
                    'moneda' => 'Moneda',
                    'monto_apertura' => 'Monto Apertura',
                ],
                'formatters' => [
                    'numero_cuenta' => 'cuenta_bancaria',
                    'monto_apertura' => 'monto',
                    'moneda' => 'moneda',
                ],
                'ignorar' => ['updated_at', 'user_updated']
            ],*/
        ];
        
        return $configs[$tabla] ?? [
            'labels' => [],
            'formatters' => [],
            'ignorar' => ['updated_at', 'user_updated', 'created_at', 'user_created']
        ];
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

        /* ================== VALIDACIÓN BASE ================== */
        $rules = [
            'tipo_entidad_sunat' => ['required'],
        ];

        /* ================== PERSONA NATURAL ================== */
        if ($request->tipo_entidad_sunat === 'NATURAL') {

            $rules = array_merge($rules, [

                // SUNAT
                'tipo_documento_input1'   => ['required'],
                'numero_documento_input1' => ['required'],

                // RAZÓN SOCIAL
                'nombre_razonsocial_input1' => ['required'],

                // PERSONA NATURAL
                'nombre_persona_natural'       => ['required'],
                'apellido_paterno_per_natural' => ['required'],
                'apellido_materno_per_natural' => ['required'],
                'sexo'                         => ['required'],
                'fecha_nacimiento'             => ['required', 'date'],
                'ruc_pers_nat'                 => ['required'],
                'tratamiento_pers_nat'         => ['required'],

                // CONTACTO GENERAL
                'celular' => ['required'],
                'email'   => ['required', 'email'],

                // DIRECCIÓN
                'direccion'    => ['required'],
                'departamento' => ['required'],
                'provincia'    => ['required'],
                'distrito'     => ['required'],
            ]);
        }

        /* ================== PERSONA JURÍDICA ================== */
        if ($request->tipo_entidad_sunat === 'JURIDICA') {

            $rules = array_merge($rules, [

                // SUNAT
                'tipo_documento_input1'   => ['required'],
                'numero_documento_input1' => ['required'],

                // RAZÓN SOCIAL
                'nombre_razonsocial_input1' => ['required'],

                // REPRESENTANTE LEGAL
                'nombre_apellidos_representante_legal' => ['required'],
                'telefono_representante'               => ['required'],

                // CONTACTO COMERCIAL
                'nombre_apellidos_contacto_comercial' => ['required'],
                'cargo_contacto_comercial'             => ['required'],
                'telefono_contacto_comercial'          => ['required'],
                'email_contacto_comercial'             => ['required', 'email'],

                // CONTACTO GENERAL
                'celular' => ['required'],
                'email'   => ['required', 'email'],

                // DIRECCIÓN
                'direccion'    => ['required'],
                'departamento' => ['required'],
                'provincia'    => ['required'],
                'distrito'     => ['required'],
            ]);
        }

        //$validated = $request->validate($rules);
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

        if (in_array($request->tipo_entidad_sunat, ['NATURAL', 'JURIDICA'])) {
         $data['estado_completoxproveedor'] = 1;
        }

        // Actualizar proveedor
        $proveedor->update($data);
                
        // 🔥 VERIFICAR SI EXISTE LOG
        $primerLog = Logbd::where('nombre_tabla', 'persona')
            ->where('id_registrotabla', $proveedor->idpersona)
            ->doesntExist();
        
        if ($primerLog) {
            // PASA LA TABLA 'persona' Y AUTOMÁTICAMENTE USA SU CONFIG
            $this->registrarSnapshot(
                $proveedor,
                'persona',  // 👈 ESTO BUSCA LA CONFIG
                $proveedor->idpersona,
                'REGISTRO_INICIAL_PROVEEDOR'
            );
        }else {
        
            $cambios = $proveedor->getChanges();
            $this->registrarCambios(
                $proveedor,
                'persona',  // 👈 MISMA TABLA = MISMA CONFIG
                $proveedor->idpersona,
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
            $tipo = 'proveedor';  // O 'cuenta_bancaria' o 'cliente'

            // Enviar el correo con los datos adecuados
            Mail::to($usuarioLogistica->email)->queue(new ProveedorActualizadoLogisticaMail($proveedor, $tipo));
        }
        

        return ApiResponse::success([
            'e' =>'Registro actualizado',
        ], 'Proveedor actualizado correctamente');
    }

    
}
