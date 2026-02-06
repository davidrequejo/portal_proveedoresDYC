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
use App\Models\PersonaFechaHomologacion;
use PhpOffice\PhpSpreadsheet\IOFactory;


class AllHomologacionesController extends Controller
{
    public function index(Request $req)
    {        
        return view('all_homologaciones');
    }

    public function crear_proveedor(Request $r)
    {
       try {
          // Validar los datos del formulario
          //              'fecha_inicio_periodo'    => 'required|date',
          //    'fecha_fin_periodo'       => 'required|date|after:fecha_inicio_periodo',
          $data = $r->validate([
              'idtipo_persona'          => 'required|string',
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
                // 4.1 obtenener nombre y correo del soporte desde el usuario autenticado
                $nombreSoporte = auth()->user()->persona?->nombre_razonsocial;
                $correoSoporte = auth()->user()->persona?->email;
              try {
                  Mail::to($createProveedor->email)->queue(
                      new CredencialesProveedorMail(
                          nombre: $createProveedor->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal,
                          nombreSoporte: $nombreSoporte,
                          correoSoporte: $correoSoporte
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

            // 4.1 obtenener nombre y correo del soporte desde el usuario autenticado
            $nombreSoporte = auth()->user()->persona?->nombre_razonsocial;
            $correoSoporte = auth()->user()->persona?->email;

            // 1. Validación
            $data = $r->validate([
                'idtipo_persona'              => 'required|string',
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
                  Mail::to($r->email)->queue(
                      new CredencialesProveedorMail(
                          nombre: $r->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal,
                          nombreSoporte: $nombreSoporte,
                          correoSoporte: $correoSoporte
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
                  Mail::to($r->email)->queue(
                      new CredencialesProveedorMail(
                          nombre: $r->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal,
                          nombreSoporte: $nombreSoporte,
                          correoSoporte: $correoSoporte
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

    public function ImportarProveedoresExcel(Request $request)
    {
        try {

            // ===============================
            // 1️⃣ Validar archivo
            // ===============================
            if (!$request->hasFile('file_excel_proveedor_masivo')) {
                return ApiResponse::validation([], 'Debe seleccionar un archivo Excel');
            }

            $file = $request->file('file_excel_proveedor_masivo');

            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['xlsx', 'xls'])) {
                return ApiResponse::validation([], 'El archivo debe ser Excel (.xlsx / .xls)');
            }

            // ===============================
            // 2️⃣ Leer Excel (FORMA CORRECTA)
            // ===============================
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();

            // ⛔ NO usar keys A,B,C
            // ✅ Usar índices 0,1,2
            $rows = $sheet->toArray('', false, false, false);

            $registros = [];
            $contador  = 0;

            // ===============================
            // 3️⃣ Recorrer filas
            // ===============================
            foreach ($rows as $i => $row) {

                // Saltar encabezado (fila 1)
                if ($i === 0) {
                    continue;
                }

                // Mapear columnas según tu plantilla REAL
                $tipoEntidadSunat = trim((string) ($row[0] ?? ''));
                $tipoDocumento    = trim((string) ($row[1] ?? ''));
                $numeroDocumento  = trim((string) ($row[2] ?? ''));
                $razonSocial      = trim((string) ($row[3] ?? ''));
                $nombres          = trim((string) ($row[4] ?? ''));
                $apePaterno       = trim((string) ($row[5] ?? ''));
                $apeMaterno       = trim((string) ($row[6] ?? ''));
                $telefono         = trim((string) ($row[7] ?? ''));
                $email            = trim((string) ($row[8] ?? ''));
                $direccion        = trim((string) ($row[9] ?? ''));

                // ===============================
                // 4️⃣ Validación mínima REAL
                // ===============================
                if (
                    !$tipoEntidadSunat ||
                    !$tipoDocumento ||
                    !$numeroDocumento ||
                    !$razonSocial
                ) {
                    continue;
                }

                // Validación solo para NATURAL
                if (strtoupper($tipoEntidadSunat) === 'NATURAL') {
                    if (!$nombres || !$apePaterno) {
                        continue;
                    }
                }

                // ===============================
                // 5️⃣ Armar registro
                // ===============================
                $registros[] = [
                    'idtipo_persona'               => 3, // fijo
                    'tipo_entidad_sunat'           => $tipoEntidadSunat,
                    'tipo_documento'               => $tipoDocumento,
                    'numero_documento'             => $numeroDocumento,
                    'nombre_razonsocial'           => $razonSocial,
                    'nombre_persona_natural'       => $nombres ?: null,
                    'apellido_paterno_per_natural' => $apePaterno ?: null,
                    'apellido_materno_per_natural' => $apeMaterno ?: null,
                    'celular'                      => $telefono ?: null,
                    'direccion'                    => $direccion ?: null,
                    'email'                        => $email ?: null,
                    'created_at'                   => now(),
                    'updated_at'                   => now(),
                ];

                $contador++;
            }

            // ===============================
            // 6️⃣ Validar si hay datos
            // ===============================
            if (count($registros) === 0) {
                return ApiResponse::validation([], 'El Excel no contiene datos válidos');
            }

            // ===============================
            // 7️⃣ Insertar en BD
            // ===============================
            DB::beginTransaction();

            foreach (array_chunk($registros, 300) as $chunk) {
                foreach ($chunk as $item) {
                    Proveedor::create($item);
                }
            }

            DB::commit();

            return ApiResponse::success(
                null,
                "Importación exitosa: {$contador} proveedores"
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error($e);
        }
    }


//-------------------------funciones que estoy utilizando----------------------


    public function listar_homologaciones_all(Request $r)
    {
        // Parámetros de entrada del request
        $perPage = (int) $r->input('per_page', 20);           // Número de elementos por página (por defecto 20)
        $page    = (int) $r->input('page', 1);                // Página actual (por defecto 1)
        $sort    = $r->input('sort', 'idpersona_facha_homologacion');            // Columna a ordenar (por defecto 'idpersona')
        $dir     = $r->input('dir', 'asc');                   // Dirección de orden ('asc' o 'desc')
        $q       = trim($r->input('q', ''));                  // Término de búsqueda global

        // Columnas válidas para ordenar
        $validSorts = [
            'descripcion', 'fecha_inicio_proceso', 'fecha_inicio_periodo_h', 'fecha_fin_periodo_h',
            'estado_homologacion', 'tipo_estandar', 'proveedor', 'comprador'
        ];

        // Si la columna para ordenar no es válida, usamos 'idpersona'
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idpersona_facha_homologacion';
        }

        // Asegurarse de que la dirección de orden sea 'asc' o 'desc'
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        // Crear la consulta base
        $query = DB::table('persona_facha_homologacion as pfh')
          ->join('docsproveedortipoestandar as docs', 'docs.idpersona_facha_homologacion', '=', 'pfh.idpersona_facha_homologacion')
          ->join('detalletipoestandarproveedor as dtp', 'dtp.iddetalletipoestandarproveedor', '=', 'docs.iddetalletipoestandarproveedor')
          ->join('tipoestandarproveedor as tep', 'tep.idtipoestandarproveedor', '=', 'dtp.idtipoestandarproveedor')
          ->join('persona as comp', 'comp.idpersona', '=', 'pfh.idpersona')
          ->join('users as u', 'u.id', '=', 'pfh.user_init_process')
          ->join('persona as pu', 'u.idpersona', '=', 'pu.idpersona')
          
          ->select( 'pfh.idpersona_facha_homologacion', 'pfh.idpersona', 'pfh.descripcion', 'pfh.fecha_inicio_proceso', 'pfh.fecha_inicio_periodo_h', 
              'pfh.fecha_fin_periodo_h', 'pfh.estado_homologacion', 'pfh.estado_trash', 'tep.descripcion as tipo_estandar','comp.nombre_razonsocial as proveedor','pu.nombre_razonsocial as comprador',
              DB::raw(" CASE WHEN COUNT(docs.iddocsproveedortipoestandar) = SUM(CASE WHEN docs.estado_revision = 'Aprobado' THEN 1 ELSE 0 END) THEN 1 ELSE 0 END as todo_aprobado ")
          )
          ->where('pfh.estado_trash', '1')
          ->where('pfh.estado_delete', '1')
          //->where('pfh.idpersona', $idpersona)
          ->groupBy(
              'pfh.idpersona_facha_homologacion',
              'pfh.descripcion',
              'pfh.fecha_inicio_proceso',
              'pfh.fecha_inicio_periodo_h',
              'pfh.fecha_fin_periodo_h',
              'pfh.estado_homologacion',
              'pfh.estado_trash',
              'tep.descripcion',
              'comp.nombre_razonsocial',
              'pu.nombre_razonsocial'
          );

        // Si hay un término de búsqueda, lo aplicamos en las columnas
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw("LOWER(pfh.descripcion) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(pfh.fecha_inicio_proceso) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(pfh.fecha_inicio_periodo_h) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(pfh.fecha_fin_periodo_h) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(pfh.estado_homologacion) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(pfh.estado_trash) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(tep.descripcion) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.nombre_razonsocial) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(pu.nombre_razonsocial) LIKE ?", ["%{$q}%"]);
            });
        }

        // Ordenar los resultados
        $query->orderBy($sort, $dir);

        // Paginación
        $homologaciones_all = $query->paginate($perPage, ['*'], 'page', $page);

        // Formatear los resultados antes de devolverlos
        $homologaciones_all->getCollection()->transform(function ($d_homologacion) {
            return [
                'idpersona_facha_homologacion' => $d_homologacion->idpersona_facha_homologacion,
                'descripcion'                  => $d_homologacion->descripcion,
                'fecha_inicio_proceso'         => $d_homologacion->fecha_inicio_proceso,
                'fecha_inicio_periodo_h'       => $d_homologacion->fecha_inicio_periodo_h,
                'fecha_fin_periodo_h'          => $d_homologacion->fecha_fin_periodo_h,
                'estado_homologacion'          => $d_homologacion->estado_homologacion,
                'estado_trash'                 => $d_homologacion->estado_trash,
                'tipo_estandar'                => $d_homologacion->tipo_estandar,
                'proveedor'                    => $d_homologacion->proveedor,
                'comprador'                    => $d_homologacion->comprador, 
                'todo_aprobado'                => $d_homologacion->todo_aprobado, 
            ];
        });

        // Devolver la respuesta JSON con los resultados
        return response()->json([
            'data'         => $homologaciones_all->items(),
            'current_page' => $homologaciones_all->currentPage(),
            'per_page'     => $homologaciones_all->perPage(),
            'total'        => $homologaciones_all->total(),
            'last_page'    => $homologaciones_all->lastPage(),
            'from'         => $homologaciones_all->firstItem(),
            'to'           => $homologaciones_all->lastItem(),
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


    public function select2estadohomologacion()
    {
      try {
        $data  = PersonaFechaHomologacion::select2estado_homologacion();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->estado_homologacion.'" >' . e($t->estado_homologacion). '</option>';
        }

        return ApiResponse::success($options, 'Tipo Estandar obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }
   // ->select('pfh.idpersona','p.nombre_razonsocial','p.numero_documento','sd.abreviatura as tipodocumento')

    public function select2proveedoreshomologacion()
    {
      try {
        $data  = PersonaFechaHomologacion::select2proveedor();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idpersona.'" >' . e($t->nombre_razonsocial). ' - ('.e($t->tipodocumento).' '.e($t->numero_documento).')</option>';
        }

        return ApiResponse::success($options, 'Tipo Estandar obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }


        public function select2compradoreshomologacion()
    {
      try {
        $data  = PersonaFechaHomologacion::select2usuarioproceso();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idpersona.'" >' . e($t->nombre_razonsocial).'</option>';
        }

        return ApiResponse::success($options, 'Tipo Estandar obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }







}
