<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\DB; 
use App\Models\Tipo_estandar;
use Illuminate\Support\Facades\Mail;
use App\Mail\CredencialesProveedorMail;
use App\Models\DocsProveedorTipoEstandar;
use App\Mail\EstadoDocumentoLogisticaMail;
use App\Models\FechaHomologacion;


class ProveedorController extends Controller
{
    public function index(Request $req)
    {        
        return view('proveedor');
    }

    public function crear_proveedor(Request $r)
    {
       try {
          // Validar los datos del formulario
          $data = $r->validate([
              'fecha_inicio_periodo'    => 'required|date',
              'fecha_fin_periodo'       => 'required|date|after:fecha_inicio_periodo',
              'idtipo_persona'          => 'required|string',
              'idtipoestandarproveedor' => 'required|string',
              'tipo_entidad_sunat'      => 'required|string',
              'tipo_documento'          => 'required|string',
              'numero_documento'        => 'required|string',
              'nombre_razonsocial'      => 'required|string|max:255',
              'nombre_persona_natural'  => 'nullable|string|max:255',
              'apellido_paterno_per_natural' => 'nullable|string|max:255',
              'apellido_materno_per_natural' => 'nullable|string|max:255',
              'celular'                 => 'required|string|max:15',
              'direccion'               => 'required|string|max:255',
              'email'                   => 'required|email',
              'distrito'                => 'nullable',
              'provincia'               => 'nullable',
              'departamento'            => 'nullable',
              'usuario_portal'          => 'nullable',
              'clave_portal'            => 'nullable|string|min:6',

          ]);
          // 1. Crear  proveedor
          $createProveedor = Proveedor::create($data);

          // 2. Crear usuario para el proveedor
          /*$user = User::create([
              'idpersona' =>  $createProveedor->idpersona,
              'name'      => 'PPROVEEDOR', 
              'email'     => $r->usuario_portal,
              'password'  => bcrypt($r->clave_portal)
          ]);

          // 3. Registrar permisos en tabla intermedia
          if ($user->id   ) {

                  DB::table('usuario_permiso')->insert([
                      'users_id' => $user->id,
                      'idpermiso' => '10'
                  ]);
              
          }*/


          // 2. Crear usuario para el proveedor solo si los campos no son vacíos
          if (!empty($r->usuario_portal) && !empty($r->clave_portal)) {

              $user = User::create([
                  'idpersona' => $createProveedor->idpersona,
                  'name'      => 'PPROVEEDOR', 
                  'email'     => $r->usuario_portal,
                  'password'  => bcrypt($r->clave_portal),
              ]);

              // 3. Registrar permisos en tabla intermedia si el usuario fue creado
              $permisos = [8, 9];

              if ($user->id) {
                  $data = [];

                  foreach ($permisos as $permiso) {
                      $data[] = [
                          'users_id' => $user->id,
                          'idpermiso' => $permiso,
                      ];
                  }

                  DB::table('usuario_permiso')->insert($data);
              }

              // 4. Enviar correo con credenciales al proveedor
              try {
                  Mail::to($createProveedor->email)->send(
                      new CredencialesProveedorMail(
                          nombre: $createProveedor->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal
                      )
                  );
              } catch (\Throwable $e) {
                  // opcional: log
                  \Log::error("Error enviando correo proveedor: " . $e->getMessage());
              }

          }

          return ApiResponse::success([
                'idpersona' => $createProveedor->idpersona
            ], 'Proveedor creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_proveedor(Request $r, $idpersona)
    {
        try {

            // 1. Validación
            $data = $r->validate([
                'fecha_inicio_periodo'        => 'required|date',
                'fecha_fin_periodo'           => 'required|date|after:fecha_inicio_periodo',
                'idtipo_persona'              => 'required|string',
                'idtipoestandarproveedor'     => 'required|string',
                'tipo_entidad_sunat'          => 'required|string',
                'tipo_documento'              => 'required|string',
                'numero_documento'            => 'required|string',
                'nombre_razonsocial'          => 'required|string|max:255',
                'nombre_persona_natural'      => 'nullable|string|max:255',
                'apellido_paterno_per_natural' => 'nullable|string|max:255',
                'apellido_materno_per_natural' => 'nullable|string|max:255',
                'celular'                     => 'required|string|max:15',
                'direccion'                   => 'required|string|max:255',
                'email'                       => 'required|email',
                'distrito'                    => 'nullable',
                'provincia'                   => 'nullable|string',
                'departamento'                => 'nullable|string',
                'usuario_portal'              => 'nullable|string',
                'clave_portal'                => 'nullable|string|min:6', // 👈 CLAVE OPCIONAL
                'id'                          => 'nullable|integer'       // 👈 ID DEL USUARIO
            ]);

            // 2. Actualizar proveedor
            $proveedor = Proveedor::where('idpersona', $idpersona)->firstOrFail();
            $proveedor->update($data);

            // 3. Actualizar usuario
            /*$user = User::findOrFail($r->id);

            $user->email = $r->usuario_portal;

            // 👉 SOLO si la clave viene llena
            if (!empty($r->clave_portal)) {
                $user->password = bcrypt($r->clave_portal);
            }

            $user->save();*/

            // 3. Verificar si el ID del usuario fue proporcionado
            if ($r->id) {
                // Si el ID fue proporcionado, actualizamos el usuario
                $user = User::findOrFail($r->id);
                $user->email = $r->usuario_portal;

                // 👉 SOLO si la clave viene llena
                if (!empty($r->clave_portal)) {
                    $user->password = bcrypt($r->clave_portal);
                }

                $user->save();

              // 2. Enviar correo con credenciales al proveedor
              try {
                  Mail::to($r->email)->send(
                      new CredencialesProveedorMail(
                          nombre: $r->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal
                      )
                  );
              } catch (\Throwable $e) {
                  // opcional: log
                  \Log::error("Error enviando correo proveedor: " . $e->getMessage());
              }

            } else {
              // Si el ID no fue proporcionado, creamos un nuevo usuario
              $user = User::create([
                  'idpersona' => $proveedor->idpersona,
                  'name'      => 'PPROVEEDOR', 
                  'email'     => $r->usuario_portal,
                  'password'  => bcrypt($r->clave_portal) // Solo si se proporciona la clave
              ]);

              // 3. Registrar permisos en tabla intermedia si el usuario fue creado
              if ($user->id) {
                  DB::table('usuario_permiso')->insert([
                      'users_id' => $user->id,
                      'idpermiso' => '10',
                  ]);
              }

              // 4. Enviar correo con credenciales al proveedor
              try {
                  Mail::to($r->email)->send(
                      new CredencialesProveedorMail(
                          nombre: $r->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal
                      )
                  );
              } catch (\Throwable $e) {
                  // opcional: log
                  \Log::error("Error enviando correo proveedor: " . $e->getMessage());
              }
            }



            return ApiResponse::success([
                'idpersona' => $proveedor->idpersona,
                'users_id'  => $user->id
            ], 'Proveedor actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function mostrar_proveedor($idpersona)
    {
        try {

            // 1. Buscar proveedor por idpersona
            $proveedor = Proveedor::where('idpersona', $idpersona)->firstOrFail();

            // 2. Buscar usuario asociado (opcional, si editas usuario)
            $usuario = User::where('idpersona', $idpersona)->first();

            if (!$usuario) { $usuario = null; }

            return ApiResponse::success([
                'proveedor' => $proveedor,
                'usuario'   => $usuario
            ], 'Proveedor encontrado');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function eliminar_proveedor($idpersona)
    {
        DB::beginTransaction();

        try {

            // 1. Desactivar proveedor
            $proveedor = Proveedor::where('idpersona', $idpersona)->firstOrFail();
            $proveedor->estado = 0;
            $proveedor->save();

            // 2. Desactivar usuario asociado
            $user = User::where('idpersona', $idpersona)->first();

            if ($user) {
                $user->estado_trash = 0;
                $user->save();
            }

            DB::commit();

            return ApiResponse::success([
                'idpersona' => $idpersona
            ], 'Proveedor y usuario desactivados correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error($e);
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
    public function selec2tipoEstandar()
    {
      try {
        $data  = Tipo_estandar::select2tipoestandar();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idtipoestandarproveedor.'" >' . e($t->descripcion). '</option>';
        }

        return ApiResponse::success($options, 'Tipo Estandar obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }

    
    public function selec2periodohomologacion()
    {
      try {
        $data  = FechaHomologacion::select2Homologacion();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idfecha_homologacion.'" >' . e($t->descripcion). ' ('. e($t->fecha_inicio) .' - '. e($t->fecha_fin) .' )</option>';
        }

        return ApiResponse::success($options, 'Lista Homologaciones obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }






    // listar tipos de estandar y documentos asociados
    public function listar_tipos_estandar_docs(Request $r)
    {
        try {
            $id = $r->input('idtipoestandar');
            $idpersona = $r->input('idpersona');

            $data = DB::table('tipoestandarproveedor as est')
                ->join(
                    'detalletipoestandarproveedor as det',
                    'est.idtipoestandarproveedor',
                    '=',
                    'det.idtipoestandarproveedor'
                )
                ->leftJoin('docsproveedortipoestandar as doc', function ($join) use ($idpersona) {
                    $join->on(
                        'det.iddetalletipoestandarproveedor',
                        '=',
                        'doc.iddetalletipoestandarproveedor'
                    )
                    ->where('doc.idpersona', $idpersona); // 🔥 FILTRO REAL
                })
                ->select(
                    'det.iddetalletipoestandarproveedor',
                    'det.idtipoestandarproveedor',
                    'doc.iddocsproveedortipoestandar',
                    'doc.idpersona',
                    'det.detalle',
                    'det.estado_trash',
                    'det.estado_delete',
                    'doc.nombreDocumento',
                    'doc.archivo',
                    'doc.estado_revision',
                    'doc.estado_trash as estado_trashDocs',
                    'doc.estado_delete as estado_deleteDocs'
                )
                ->where('est.idtipoestandarproveedor', $id)
                ->get();

            return ApiResponse::success($data, 'Tipos de estandar y documentos obtenidos');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    
    // actualizar estado y observacion del documento estandar
    public function actualizar_estado_doc_estandar(Request $r, $iddocsproveedortipoestandar)
    {
        try {

            // 1) Buscar documento y validar pertenencia
            $doc = DocsProveedorTipoEstandar::where('iddocsproveedortipoestandar', $iddocsproveedortipoestandar)
                ->firstOrFail();

            // 2) Actualizar SOLO los campos permitidos
            $doc->estado_revision = $r->input('estado_documentos_update');
            $doc->observacion     = $r->input('observacion_est_up');

            // 3) Guardar cambios
            $doc->save();


            // 2. Obtener proveedor
            $proveedor = Proveedor::where('idpersona', $doc->idpersona)->first();

            // 3. Usuario de logística (quien hace la acción)
            $usuarioLogistica = auth()->user();

            // 4. Enviar correo al proveedor
            Mail::to($proveedor->email)->send(
                new EstadoDocumentoLogisticaMail(
                    $proveedor,
                    $doc->nombreDocumento,
                    $doc->estado_revision,
                    $doc->observacion,
                    $usuarioLogistica
                )
            );

            return ApiResponse::success([], 'Estado actualizado y correo enviado');

            //return ApiResponse::success('Actualizado', 'Estado del documento actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }



}
