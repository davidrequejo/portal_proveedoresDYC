<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\PersonaCuentaBancaria;
use App\Models\Banco;
use App\Models\Logbd;
use App\Models\Persona;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacioncuentaBancariaLogisticaMail;
use App\Traits\RegistraLogCompleto; // 👈 1. IMPORTAR

class PersonaCuentaBancariaController extends Controller
{
    use RegistraLogCompleto;   // 👈 2. USAR EL TRAIT
    /*public function index(Request $r)
    {
        return view('PersonaCuentaBancaria');
    }*/

    public function getConfigLog($tabla)
    {
        $configs = [
            // ... tu configuración de 'persona' ...
            
            // 🔥 NUEVA CONFIGURACIÓN PARA CUENTAS BANCARIAS
            'persona_cuentabancaria' => [
                'labels' => [
                    'idbanco'              => 'Banco',
                    'banco_nombre'         => 'Banco',      // Campo virtual
                    'banco_ids10'          => 'Código S10', // Campo virtual
                    'tipocuenta'           => 'Tipo Cuenta',
                    'moneda'               => 'Moneda',
                    'numero_cuenta'        => 'N° Cuenta',
                    'numero_cuenta_abono'  => 'N° Cuenta Abono',
                    'cuenta_interbancaria' => 'CCI',
                    'predeterminado'       => 'Predeterminado',
                ],
                'formatters' => [
                    'idbanco'        => 'banco_completo',
                    'tipocuenta'     => 'tipo_cuenta',
                    'moneda'         => 'moneda_simbolo',
                    'predeterminado' => 'booleano_si_no',
                    'numero_cuenta'  => 'cuenta_bancaria',
                    'numero_cuenta_abono' => 'cuenta_bancaria',
                    'cuenta_interbancaria' => 'cci',
                ],
                'ignorar' => ['updated_at', 'user_updated', 'created_at', 'user_created']
            ],
        ];
        
        return $configs[$tabla] ?? ['labels' => [], 'formatters' => []];
    }

    /* =========================
     * CREAR
     * ========================= */
    public function crear(Request $r)
    {
        try {

            $data = $r->validate([
                'idpersona'            => 'required|integer',
                'idbanco'              => 'required|integer',
                'tipocuenta'           => 'required|string|max:45',
                'moneda'               => 'required|string|max:5',
                'numero_cuenta'        => 'required|string|max:45',
                'numero_cuenta_abono'  => 'nullable|string|max:45',
                'cuenta_interbancaria' => 'nullable|string|max:45',
                'predeterminado'       => 'required|in:0,1',
            ]);

            // Si es predeterminada, quitar a las demás
            if ($r->predeterminado === '1') {
                PersonaCuentaBancaria::where('idpersona', $r->idpersona)
                    ->update(['predeterminado' => '0']);
            }

            $cuenta = PersonaCuentaBancaria::create([
                'idpersona'            => $r->idpersona,
                'idbanco'              => $r->idbanco,
                'tipocuenta'           => $r->tipocuenta,
                'moneda'               => $r->moneda,
                'numero_cuenta'        => $r->numero_cuenta,
                'numero_cuenta_abono'  => $r->numero_cuenta_abono,
                'cuenta_interbancaria' => $r->cuenta_interbancaria,
                'predeterminado'       => $r->predeterminado,
                'user_created'         => auth()->id() ?? null,
            ]);

            // Cargar relación con banco
            $cuenta->load('banco');

            // Guardar snapshot
            $this->registrarSnapshot(
                $cuenta,
                'persona_cuentabancaria',
                $cuenta->idpersona_cuentabancaria,
                'REGISTRO_INICIAL_CUENTA_BANCARIA'
            );


            //obtenermos clieente o proveedor
            $cliente_proveedor = Persona::findOrFail($cuenta->idpersona);

            $tipo_persona = $cliente_proveedor->idtipo_persona==3 ? 'proveedor' : 'cliente';

            //    3	PROVEEDOR
            //    5	CLIENTE

            /** Enviar correo de notificación a logística */
            $logistica = DB::table('persona')
            ->join('tipo_persona', 'persona.idtipo_persona', '=', 'tipo_persona.idtipo_persona')
            ->where('persona.idtipo_persona', 6)
            ->where('persona.estado', 1)
            ->where('persona.estado_delete', 1)
            ->select('persona.idpersona', 'persona.nombre_razonsocial', 'persona.email', 'tipo_persona.descripcion')
            ->get();

            foreach ($logistica as $usuarioLogistica) {

                // Enviar el correo con los datos adecuados
                Mail::to($usuarioLogistica->email)->queue(new NotificacioncuentaBancariaLogisticaMail($cliente_proveedor,$cuenta, $tipo_persona, 'agregar') );
            }

            return ApiResponse::success([
                'cuentas' => 'Cuenta Bancaria creada',
            ], 'Cuenta bancaria creada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    /* =========================
     * EDITAR
     * ========================= */
    public function editar(Request $r, int $id)
    {
        try {

            $data = $r->validate([
                'idbanco'              => 'required|integer',
                'tipocuenta'           => 'required|string|max:45',
                'moneda'               => 'required|string|max:5',
                'numero_cuenta'        => 'required|string|max:45',
                'numero_cuenta_abono'  => 'nullable|string|max:45',
                'cuenta_interbancaria' => 'nullable|string|max:45',
                'predeterminado'       => 'required|in:0,1',
            ]);

            $cuenta = PersonaCuentaBancaria::findOrFail($id);

            if ($r->predeterminado === '1') {
                PersonaCuentaBancaria::where('idpersona', $cuenta->idpersona)
                    ->where('idpersona_cuentabancaria', '!=', $id)
                    ->update(['predeterminado' => '0']);
            }

            $cuenta->update([
                'idbanco'              => $r->idbanco,
                'tipocuenta'           => $r->tipocuenta,
                'moneda'               => $r->moneda,
                'numero_cuenta'        => $r->numero_cuenta,
                'numero_cuenta_abono'  => $r->numero_cuenta_abono,
                'cuenta_interbancaria' => $r->cuenta_interbancaria,
                'predeterminado'       => $r->predeterminado,
                'user_updated'         => auth()->id() ?? null,
            ]);

            // Obtener valores después de la actualización
            $cambios = $cuenta->getChanges();

            // Solo proceder si hubo cambios reales
            if (!empty($cambios)) {
                // Cargar relación con banco
                $cuenta->load('banco');

                // 🔥 AGREGAR CAMPOS VIRTUALES PARA EL LOG (solo si hay cambios)
                $cambios['idbanco'] = $cuenta->idbanco;

                // También podrías agregar otros campos virtuales si los necesitas
                // $cambios['banco_nombre'] = $cuenta->banco?->nombre;
                // $cambios['banco_ids10'] = $cuenta->banco?->codigo_bank_s10;

                $this->registrarCambios(
                    $cuenta,
                    'persona_cuentabancaria',
                    $cuenta->idpersona_cuentabancaria,
                    $cambios,
                    'ACTUALIZAR'
                );
            }


            //obtenermos clieente o proveedor
            $cliente_proveedor = Persona::findOrFail($cuenta->idpersona);

            $tipo_persona = $cliente_proveedor->idtipo_persona==3 ? 'proveedor' : 'cliente';

            //    3	PROVEEDOR
            //    5	CLIENTE

            /** Enviar correo de notificación a logística */
            $logistica = DB::table('persona')
            ->join('tipo_persona', 'persona.idtipo_persona', '=', 'tipo_persona.idtipo_persona')
            ->where('persona.idtipo_persona', 6)
            ->where('persona.estado', 1)
            ->where('persona.estado_delete', 1)
            ->select('persona.idpersona', 'persona.nombre_razonsocial', 'persona.email', 'tipo_persona.descripcion')
            ->get();

            foreach ($logistica as $usuarioLogistica) {

                // Enviar el correo con los datos adecuados
                Mail::to($usuarioLogistica->email)->queue(new NotificacioncuentaBancariaLogisticaMail($cliente_proveedor,$cuenta, $tipo_persona, 'editar') );
            }

            return ApiResponse::success([
                'update' =>  'cambios'
            ], 'Cuenta bancaria actualizada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    /* =========================
     * ELIMINAR (LÓGICO)
     * ========================= */
    public function eliminar(Request $r, int $id)
    {
        try {

            $cuenta = PersonaCuentaBancaria::findOrFail($id);

            $cuenta->update([
                'estado_trash' => '0',
                'user_delete'  => auth()->id() ?? null,
            ]);

            $cambios = $cuenta->getChanges();

            // Cargar relación con banco
            $cuenta->load('banco');

            // 🔥 AGREGAR CAMPOS VIRTUALES PARA EL LOG (solo si hay cambios)
            $cambios['idbanco'] = $cuenta->idbanco;


            $this->registrarCambios(
                $cuenta,
                'persona_cuentabancaria',
                $cuenta->idpersona,
                $cambios,
                'ELIMINADO'
            );


            //obtenermos clieente o proveedor
            $cliente_proveedor = Persona::findOrFail($cuenta->idpersona);

            $tipo_persona = $cliente_proveedor->idtipo_persona==3 ? 'proveedor' : 'cliente';

            //    3	PROVEEDOR
            //    5	CLIENTE

            /** Enviar correo de notificación a logística */
            $logistica = DB::table('persona')
            ->join('tipo_persona', 'persona.idtipo_persona', '=', 'tipo_persona.idtipo_persona')
            ->where('persona.idtipo_persona', 6)
            ->where('persona.estado', 1)
            ->where('persona.estado_delete', 1)
            ->select('persona.idpersona', 'persona.nombre_razonsocial', 'persona.email', 'tipo_persona.descripcion')
            ->get();

            foreach ($logistica as $usuarioLogistica) {

                // Enviar el correo con los datos adecuados
                Mail::to($usuarioLogistica->email)->queue(new NotificacioncuentaBancariaLogisticaMail($cliente_proveedor,$cuenta, $tipo_persona, 'desactivar') );
            }


            return ApiResponse::success([
                'idpersona_cuentabancaria' => $id,
                'estado_trash' => 0
            ], 'Cuenta bancaria eliminada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    /* =========================
     * LISTAR
     * ========================= */
    public function listar(Request $r)
    {
        $idpersona = $r->user()->idpersona; // usuario logueado

        $query = DB::table('persona_cuentabancaria as pcb')
            ->join('banco as b', 'b.idbanco', '=', 'pcb.idbanco')
            ->select(
                'pcb.idpersona_cuentabancaria',
                'pcb.idpersona',
                'pcb.tipocuenta',
                'pcb.moneda',
                'pcb.numero_cuenta',
                'pcb.numero_cuenta_abono',
                'pcb.cuenta_interbancaria',
                'pcb.predeterminado',
                'pcb.estado_trash',
                'b.descripcion as banco'
            )
            ->where('pcb.estado_trash', '1')
            ->where('pcb.estado_delete', '1');

        if ($idpersona) {
            $query->where('pcb.idpersona', $idpersona);
        }

        return response()->json([
            'data' => $query->get()
        ]);
    }

    /* =========================
     * MOSTRAR
     * ========================= */
    public function mostrar(Request $r, int $id)
    {
        try {

            $cuenta = PersonaCuentaBancaria::with('banco')
                ->whereKey($id)
                ->firstOrFail();

            return ApiResponse::success($cuenta, 'Cuenta bancaria obtenida correctamente');

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(
                new \Exception('Cuenta bancaria no encontrada', 404),
                404
            );

        } catch (\Throwable $e) {
            return ApiResponse::error($e, 500);
        }
    }

    public function Listar_Proveedores(Request $r)
    {
        // Parámetros de entrada del request
        $perPage = (int) $r->input('per_page', 20);           // Número de elementos por página (por defecto 20)
        $page    = (int) $r->input('page', 1);                // Página actual (por defecto 1)
        $sort    = $r->input('sort', 'idpersona');            // Columna a ordenar (por defecto 'idpersona')
        $dir     = $r->input('dir', 'asc');                   // Dirección de orden ('asc' o 'desc')
        $q       = trim($r->input('q', ''));                  // Término de búsqueda global

        // Columnas válidas para ordenar
        $validSorts = [
            'idpersona', 'nombre_razonsocial', 'apellidos_nombrecomercial', 'tipo_documento',
            'numero_documento', 'celular', 'direccion', 'distrito', 'provincia', 'departamento', 'email','tipo_entidad_sunat','estado'
        ];

        // Si la columna para ordenar no es válida, usamos 'idpersona'
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idpersona';
        }

        // Asegurarse de que la dirección de orden sea 'asc' o 'desc'
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        // Crear la consulta base
        $query = DB::table('persona as p')
            ->join('tipo_persona as tp', 'p.idtipo_persona', '=', 'tp.idtipo_persona')
            ->join('sunat_c06_doc_identidad as doc', 'p.tipo_documento', '=', 'doc.code_sunat')
            ->join('tipoestandarproveedor as test', 'p.idtipoestandarproveedor', '=', 'test.idtipoestandarproveedor')

            // 🔥 LEFT JOIN a documentos
            ->leftJoin('docsproveedortipoestandar as d', function ($join) {
                $join->on('p.idpersona', '=', 'd.idpersona')
                    ->where('d.estado_revision', '=', 'Aprobado');
            })

            ->select(
                'p.idpersona',
                'p.idtipoestandarproveedor',
                'tp.descripcion as tipoPersona',
                'p.tipo_documento',
                'p.nombre_razonsocial',
                'p.apellidos_nombrecomercial',
                'doc.abreviatura',
                'p.numero_documento',
                'p.celular',
                'p.direccion',
                'p.distrito',
                'p.provincia',
                'p.departamento',
                'p.email',
                'p.tipo_entidad_sunat',
                'test.descripcion as tipo_estandar',
                'test.nroDocumentos',
                'p.estado',

                // ✅ CONTEO FINAL
                DB::raw('COUNT(d.iddocsproveedortipoestandar) as total_docs_registrados')
            )

            ->where('p.estado', '1')
            ->where('p.estado_delete', '1')

            // 🔑 CLAVE: agrupar por persona
            ->groupBy(
                'p.idpersona',
                'p.idtipoestandarproveedor',
                'tp.descripcion',
                'p.tipo_documento',
                'p.nombre_razonsocial',
                'p.apellidos_nombrecomercial',
                'doc.abreviatura',
                'p.numero_documento',
                'p.celular',
                'p.direccion',
                'p.distrito',
                'p.provincia',
                'p.departamento',
                'p.email',
                'p.tipo_entidad_sunat',
                'test.descripcion',
                'test.nroDocumentos',
                'p.estado'
            );

        // Si hay un término de búsqueda, lo aplicamos en las columnas
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw("LOWER(p.nombre_razonsocial) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.apellidos_nombrecomercial) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.tipo_documento) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.numero_documento) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.celular) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.direccion) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.distrito) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.provincia) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.departamento) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.email) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.tipo_entidad_sunat) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.estado) LIKE ?", ["%{$q}%"]);
            });
        }

        // Ordenar los resultados
        $query->orderBy($sort, $dir);

        // Paginación
        $proveedores = $query->paginate($perPage, ['*'], 'page', $page);

        // Formatear los resultados antes de devolverlos
        $proveedores->getCollection()->transform(function ($proveedor) {
            return [
                'idpersona'             => $proveedor->idpersona,
                'idtipoestandarproveedor'=> $proveedor->idtipoestandarproveedor,
                'tipoPersona'           => $proveedor->tipoPersona,
                'tipo_documento'        => $proveedor->tipo_documento,
                'nombre_razonsocial'    => $proveedor->nombre_razonsocial,
                'apellidos_nombrecomercial' => $proveedor->apellidos_nombrecomercial,
                'abreviatura'           => $proveedor->abreviatura,
                'numero_documento'     => $proveedor->numero_documento,
                'celular'               => $proveedor->celular,
                'direccion'             => $proveedor->direccion,
                'distrito'              => $proveedor->distrito,
                'provincia'             => $proveedor->provincia,
                'departamento'          => $proveedor->departamento,
                'email'                 => $proveedor->email,
                'tipo_entidad_sunat'    => $proveedor->tipo_entidad_sunat,
                'tipo_estandar'         => $proveedor->tipo_estandar,
                'nroDocumentos'         => $proveedor->nroDocumentos,
                'total_docs_registrados' => $proveedor->total_docs_registrados,
                'estado'                => $proveedor->estado,
            ];
        });

        // Devolver la respuesta JSON con los resultados
        return response()->json([
            'data'         => $proveedores->items(),
            'current_page' => $proveedores->currentPage(),
            'per_page'     => $proveedores->perPage(),
            'total'        => $proveedores->total(),
            'last_page'    => $proveedores->lastPage(),
            'from'         => $proveedores->firstItem(),
            'to'           => $proveedores->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    // Método para obtener todos roles personas
    public function selec2banco()
    {
      try {
        $data  = Banco::select2Bancos();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idbanco.'" >' . e($t->descripcion). '</option>';
        }

        return ApiResponse::success($options, 'Tipo Estandar obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }
}
