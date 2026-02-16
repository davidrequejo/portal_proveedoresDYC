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
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cookie;
use ZipStream\ZipStream;
use ZipStream\OperationMode;
use Illuminate\Support\Facades\Log;

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
        // Parámetros de entrada
        $perPage = (int) $r->input('per_page', 20);
        $page    = (int) $r->input('page', 1);
        $sort    = $r->input('sort', 'idpersona_facha_homologacion');
        $dir     = $r->input('dir', 'asc');
        $q       = trim($r->input('q', ''));

        // Filtros
        $tipo_compra          = $r->input('tipo_compra');
        $fecha_inicio_periodo = $r->input('fecha_inicio_periodo');
        $fecha_fin_periodo    = $r->input('fecha_fin_periodo');
        $estado_homologacion  = $r->input('estado_homologacion');
        $id_proveedor         = $r->input('id_proveedor');
        $id_persona_usuario   = $r->input('id_persona_usuario');

        // Columnas válidas para ordenar
        $validSorts = [
            'descripcion',
            'fecha_inicio_proceso',
            'fecha_inicio_periodo_h',
            'fecha_fin_periodo_h',
            'estado_homologacion',
            'tipo_estandar',
            'proveedor',
            'comprador'
        ];

        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idpersona_facha_homologacion';
        }

        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        /* ====================== QUERY BASE ====================== */

        $query = DB::table('persona_facha_homologacion as pfh')
            ->join('docsproveedortipoestandar as docs', 'docs.idpersona_facha_homologacion', '=', 'pfh.idpersona_facha_homologacion')
            ->join('detalletipoestandarproveedor as dtp', 'dtp.iddetalletipoestandarproveedor', '=', 'docs.iddetalletipoestandarproveedor')
            ->join('tipoestandarproveedor as tep', 'tep.idtipoestandarproveedor', '=', 'dtp.idtipoestandarproveedor')
            ->join('persona as comp', 'comp.idpersona', '=', 'pfh.idpersona')
            ->join('users as u', 'u.id', '=', 'pfh.user_init_process')
            ->join('persona as pu', 'u.idpersona', '=', 'pu.idpersona')
            ->select(
                'pfh.idpersona_facha_homologacion',
                'pfh.idpersona',
                'pfh.descripcion',
                'pfh.fecha_inicio_proceso',
                'pfh.fecha_inicio_periodo_h',
                'pfh.fecha_fin_periodo_h',
                'pfh.estado_homologacion',
                'pfh.estado_trash',
                'tep.descripcion as tipo_estandar',
                'comp.nombre_razonsocial as proveedor',
                'pu.nombre_razonsocial as comprador',
                DB::raw("
                    CASE 
                        WHEN COUNT(docs.iddocsproveedortipoestandar)
                          = SUM(CASE WHEN docs.estado_revision = 'Aprobado' THEN 1 ELSE 0 END)
                        THEN 1 ELSE 0
                    END as todo_aprobado
                ")
            )
            ->where('pfh.estado_trash', '1')
            ->where('pfh.estado_delete', '1');

        /* ====================== FILTROS ====================== */

        if ($r->filled('tipo_compra')) { $query->where('tep.idtipoestandarproveedor', $tipo_compra); }

        if ($r->filled('estado_homologacion')) { $query->where('pfh.estado_homologacion', $estado_homologacion); }

        if ($r->filled('id_proveedor')) { $query->where('pfh.idpersona', $id_proveedor); }

        if ($r->filled('id_persona_usuario')) { $query->where('pfh.user_init_process', $id_persona_usuario); }

        // Fechas
        if ($fecha_inicio_periodo && $fecha_fin_periodo) { $query->whereBetween('pfh.fecha_inicio_proceso', [ $fecha_inicio_periodo, $fecha_fin_periodo ]); } 
        elseif ($fecha_inicio_periodo) { $query->whereDate('pfh.fecha_inicio_proceso', '>=', $fecha_inicio_periodo);
        } elseif ($fecha_fin_periodo) { $query->whereDate('pfh.fecha_inicio_proceso', '<=', $fecha_fin_periodo); }

        /* ====================== SEARCH GLOBAL ====================== */

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw("LOWER(pfh.descripcion) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("LOWER(pfh.fecha_inicio_proceso) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("LOWER(pfh.fecha_inicio_periodo_h) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("LOWER(pfh.fecha_fin_periodo_h) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("LOWER(pfh.estado_homologacion) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("LOWER(tep.descripcion) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("LOWER(comp.nombre_razonsocial) LIKE ?", ["%{$q}%"])
                  ->orWhereRaw("LOWER(pu.nombre_razonsocial) LIKE ?", ["%{$q}%"]);
            });
        }

        /* ====================== GROUP + ORDER + PAGINATE ====================== */

        $query->groupBy(
            'pfh.idpersona_facha_homologacion',
            'pfh.idpersona',
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

        $query->orderBy($sort, $dir);

        $homologaciones_all = $query->paginate($perPage, ['*'], 'page', $page);

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
            $options .= '<option value="'.$t->iduser.'" >' . e($t->nombre_razonsocial).'</option>';
        }

        return ApiResponse::success($options, 'Tipo Estandar obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }

    public function descargarDocumentos($id)
    {
        // 1️⃣ Obtener documentos
        $documentos = DB::table('docsproveedortipoestandar as d')
            ->join('detalletipoestandarproveedor as dte', 'dte.iddetalletipoestandarproveedor', '=', 'd.iddetalletipoestandarproveedor')
            ->join('tipoestandarproveedor as te', 'te.idtipoestandarproveedor', '=', 'dte.idtipoestandarproveedor')
            ->join('documento_tipo_estandar as docte', 'docte.iddocumento_tipo_estandar', '=', 'dte.iddocumento_tipo_estandar')
            ->join('persona_facha_homologacion as pfh', 'pfh.idpersona_facha_homologacion', '=', 'd.idpersona_facha_homologacion')
            ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
            ->where('d.idpersona_facha_homologacion', $id)
            ->select(
                'd.iddocsproveedortipoestandar',
                'pfh.idpersona_facha_homologacion',
                'd.archivo',
                'p.nombre_razonsocial',
                'te.descripcion as tipoestandar',
                'docte.descripcion as descripcion_doc',
                'p.numero_documento'
            )
            ->get();

         //dd([$documentos]);

        if ($documentos->isEmpty()) {
            abort(404, 'No hay documentos para descargar');
        }

        // 2️⃣ Nombre del ZIP
        $proveedor = Str::slug($documentos->first()->nombre_razonsocial);
        $zipName = "documentos_{$proveedor}_{$id}.zip";
        $zipPath = storage_path("app/temp/{$zipName}");

        if (!File::exists(storage_path('app/temp'))) {
            File::makeDirectory(storage_path('app/temp'), 0755, true);
        }

        // 3️⃣ Crear ZIP
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($documentos as $doc) {

            if (!File::exists(public_path($doc->archivo))) {
                continue;
            }

            // nombre limpio del archivo
            $extension = pathinfo($doc->archivo, PATHINFO_EXTENSION);

            $nombreArchivo =
                Str::slug($doc->numero_documento) . '_' .
                Str::slug($doc->nombre_razonsocial) . '_' .
                Str::slug($doc->tipoestandar) . '_' .
                Str::slug($doc->descripcion_doc) .             
                '.' . $extension;

            $zip->addFile(
                public_path($doc->archivo),
                $nombreArchivo
            );
        }

        $zip->close();

        // 4️⃣ Descargar y borrar luego
       // return response()->download($zipPath)->deleteFileAfterSend(true);
        /*return response()
        ->download($zipPath)
        ->deleteFileAfterSend(true)
        ->cookie(
            'descarga_homologacion_ok',
            '1',
            1,      // minutos
            '/'
        );*/
        // ✅ Cookie correcta (ANTES del return)
        Cookie::queue(
            'descarga_homologacion_ok',
            '1',
            1,
            '/'
        );

        // ✅ return LIMPIO (SIN cookie)
        return response()
        ->download($zipPath)
        ->deleteFileAfterSend(true);
    }

    //----------------+/


     /**
     * Descarga masiva con estructura: PROVEEDOR / TIPO / FECHA / documentos
     */
    public function descargaMasiva(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        
        Log::info('Iniciando descarga masiva con estructura por fecha', $request->all());
        
        try {
            // ========== PARÁMETROS ==========
            $tipo_compra = $request->input('tipo_compra');
            $fecha_inicio = $request->input('fecha_inicio_periodo');
            $fecha_fin = $request->input('fecha_fin_periodo');
            $estado_homologacion = $request->input('estado_homologacion');
            $id_proveedor = $request->input('id_proveedor');
            $id_persona_usuario = $request->input('id_persona_usuario');
            $q = trim($request->input('q', ''));
            
            // ========== QUERY BASE ==========
            $query = DB::table('docsproveedortipoestandar as d')
                ->join('persona_facha_homologacion as pfh', 'pfh.idpersona_facha_homologacion', '=', 'd.idpersona_facha_homologacion')
                ->join('detalletipoestandarproveedor as dte', 'dte.iddetalletipoestandarproveedor', '=', 'd.iddetalletipoestandarproveedor')
                ->join('tipoestandarproveedor as te', 'te.idtipoestandarproveedor', '=', 'dte.idtipoestandarproveedor')
                ->join('documento_tipo_estandar as docte', 'docte.iddocumento_tipo_estandar', '=', 'dte.iddocumento_tipo_estandar')
                ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1');
            
            // ========== FILTROS ==========
            if ($tipo_compra) $query->where('te.idtipoestandarproveedor', $tipo_compra);
            if ($estado_homologacion) $query->where('pfh.estado_homologacion', $estado_homologacion);
            if ($id_proveedor) $query->where('pfh.idpersona', $id_proveedor);
            if ($id_persona_usuario) $query->where('pfh.user_init_process', $id_persona_usuario);
            
            // Filtro de fechas (mejorado)
            if ($fecha_inicio && $fecha_fin) {
                $query->whereBetween('pfh.fecha_inicio_proceso', [$fecha_inicio, $fecha_fin]);
            }
            
            if (!empty($q)) {
                $searchTerm = strtolower($q);
                $query->where(function ($w) use ($searchTerm) {
                    $w->where(DB::raw('LOWER(pfh.descripcion)'), 'LIKE', "%{$searchTerm}%")
                      ->orWhere(DB::raw('LOWER(te.descripcion)'), 'LIKE', "%{$searchTerm}%")
                      ->orWhere(DB::raw('LOWER(p.nombre_razonsocial)'), 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // ========== SELECCIÓN - ¡NO AGRUPAMOS POR PROVEEDOR! ==========
            $documentos = $query->select(
                'p.idpersona',
                'p.numero_documento',
                'p.nombre_razonsocial',
                'te.descripcion as tipo_homologacion',
                'docte.descripcion as descripcion_doc',
                'd.archivo',
                'd.iddocsproveedortipoestandar',
                'pfh.fecha_inicio_proceso',           // ← CLAVE: La fecha individual
                'pfh.idpersona_facha_homologacion'    // ← Para agrupar por registro
            )->get();
            
            Log::info('Documentos encontrados: ' . $documentos->count());
            
            if ($documentos->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No hay documentos'], 404);
            }
            
            // ========== CREAR ZIP CON ESTRUCTURA ==========
            $tempDir = sys_get_temp_dir();
            $zipFilename = 'homologaciones_' . now()->format('Ymd_His') . '.zip';
            $tempZipPath = $tempDir . '/' . $zipFilename;
            
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se pudo crear el ZIP');
            }
            
            $archivosAgregados = 0;
            $archivosNoEncontrados = [];
            
            // Agrupar documentos por PROVEEDOR > TIPO > FECHA
            foreach ($documentos as $doc) {
                if (empty($doc->archivo)) continue;
                
                $rutaArchivo = public_path($doc->archivo);
                if (!file_exists($rutaArchivo)) {
                    $archivosNoEncontrados[] = $doc->archivo;
                    continue;
                }
                
                // 1. CARPETA PROVEEDOR: RUC_Nombre
                $proveedor = Str::slug($doc->numero_documento . '_' . $doc->nombre_razonsocial);
                
                // 2. CARPETA TIPO: Estandar_1_Menor_Cuantia
                $tipo = Str::slug($doc->tipo_homologacion);
                
                // 3. CARPETA FECHA: YYYY-MM-DD (formato legible)
                $fecha = '';
                if (!empty($doc->fecha_inicio_proceso)) {
                    // Convertir a formato YYYY-MM-DD
                    $fechaObj = \DateTime::createFromFormat('d-m-Y', $doc->fecha_inicio_proceso);
                    if ($fechaObj) {
                        $fecha = $fechaObj->format('Y-m-d'); // 2026-02-10
                    } else {
                        // Si ya viene en otro formato
                        $fecha = date('Y-m-d', strtotime($doc->fecha_inicio_proceso));
                    }
                } else {
                    $fecha = 'sin_fecha';
                }
                
                // 4. NOMBRE ARCHIVO: descripcion_id.extension
                $extension = pathinfo($rutaArchivo, PATHINFO_EXTENSION);
                $nombreArchivo = Str::slug($doc->descripcion_doc) . '_' . 
                                 $doc->iddocsproveedortipoestandar . '.' . $extension;
                
                // ===== ESTRUCTURA FINAL =====
                // proveedor/tipo/fecha/nombre_archivo.pdf
                $rutaEnZip = $proveedor . '/' . $tipo . '/' . $fecha . '/' . $nombreArchivo;
                
                if ($zip->addFile($rutaArchivo, $rutaEnZip)) {
                    $archivosAgregados++;
                }
            }
            
            $zip->close();
            
            if ($archivosAgregados === 0) {
                if (file_exists($tempZipPath)) unlink($tempZipPath);
                return response()->json([
                    'success' => false, 
                    'message' => 'No se encontraron archivos válidos',
                    'no_encontrados' => $archivosNoEncontrados
                ], 404);
            }
            
            Log::info('ZIP creado con ' . $archivosAgregados . ' archivos');
            
            return response()->download($tempZipPath, $zipFilename, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFilename . '"',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Vista previa de la estructura que se generará
     */
    public function vistaPrevia(Request $request)
    {
        try {
            $query = DB::table('docsproveedortipoestandar as d')
                ->join('persona_facha_homologacion as pfh', 'pfh.idpersona_facha_homologacion', '=', 'd.idpersona_facha_homologacion')
                ->join('detalletipoestandarproveedor as dte', 'dte.iddetalletipoestandarproveedor', '=', 'd.iddetalletipoestandarproveedor')
                ->join('tipoestandarproveedor as te', 'te.idtipoestandarproveedor', '=', 'dte.idtipoestandarproveedor')
                ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1')
                ->limit(20)
                ->select(
                    'p.nombre_razonsocial',
                    'te.descripcion as tipo',
                    'pfh.fecha_inicio_proceso',
                    'd.iddocsproveedortipoestandar',
                    'd.archivo'
                )->get();
            
            $estructura = [];
            foreach ($query as $item) {
                $estructura[] = [
                    'ruta' => Str::slug($item->nombre_razonsocial) . '/' . 
                             Str::slug($item->tipo) . '/' . 
                             date('Y-m-d', strtotime($item->fecha_inicio_proceso)) . '/' . 
                             'documento_' . $item->iddocsproveedortipoestandar . '.pdf',
                    'archivo' => $item->archivo,
                    'existe' => file_exists(public_path($item->archivo))
                ];
            }
            
            return response()->json([
                'success' => true,
                'total' => count($estructura),
                'estructura' => $estructura,
                'ejemplo' => $estructura[0] ?? null
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }




    public function descargaMasivaold(Request $request)
    {
        // Configuración
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        
        Log::info('Iniciando descarga masiva con agrupación', $request->all());
        
        try {
            // Parámetros
            $tipo_compra = $request->input('tipo_compra');
            $fecha_inicio = $request->input('fecha_inicio_periodo');
            $fecha_fin = $request->input('fecha_fin_periodo');
            $estado_homologacion = $request->input('estado_homologacion');
            $id_proveedor = $request->input('id_proveedor');
            $id_persona_usuario = $request->input('id_persona_usuario');
            $q = trim($request->input('q', ''));
            $agrupar = $request->input('agrupar', true); // Nuevo parámetro para agrupar
            
            // Query base
            $query = DB::table('docsproveedortipoestandar as d')
                ->join('persona_facha_homologacion as pfh', 'pfh.idpersona_facha_homologacion', '=', 'd.idpersona_facha_homologacion')
                ->join('detalletipoestandarproveedor as dte', 'dte.iddetalletipoestandarproveedor', '=', 'd.iddetalletipoestandarproveedor')
                ->join('tipoestandarproveedor as te', 'te.idtipoestandarproveedor', '=', 'dte.idtipoestandarproveedor')
                ->join('documento_tipo_estandar as docte', 'docte.iddocumento_tipo_estandar', '=', 'dte.iddocumento_tipo_estandar')
                ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1');
            
            // Aplicar filtros
            if ($tipo_compra) {
                $query->where('te.idtipoestandarproveedor', $tipo_compra);
            }
            
            if ($estado_homologacion) {
                $query->where('pfh.estado_homologacion', $estado_homologacion);
            }
            
            if ($id_proveedor) {
                $query->where('pfh.idpersona', $id_proveedor);
            }
            
            if ($id_persona_usuario) {
                $query->where('pfh.user_init_process', $id_persona_usuario);
            }
            
            // **MODIFICACIÓN IMPORTANTE: Filtro de fechas mejorado**
            if ($fecha_inicio && $fecha_fin) {
                $query->where(function ($q) use ($fecha_inicio, $fecha_fin) {
                    // Caso 1: Fecha de proceso dentro del rango
                    $q->whereBetween('pfh.fecha_inicio_proceso', [$fecha_inicio, $fecha_fin])
                      // Caso 2: Fecha de período inicio dentro del rango
                      ->orWhereBetween('pfh.fecha_inicio_periodo', [$fecha_inicio, $fecha_fin])
                      // Caso 3: Fecha de período fin dentro del rango
                      ->orWhereBetween('pfh.fecha_fin_periodo', [$fecha_inicio, $fecha_fin])
                      // Caso 4: Rango de fechas contiene el período completo
                      ->orWhere(function ($q2) use ($fecha_inicio, $fecha_fin) {
                          $q2->where('pfh.fecha_inicio_periodo', '<=', $fecha_inicio)
                             ->where('pfh.fecha_fin_periodo', '>=', $fecha_fin);
                      });
                });
            } elseif ($fecha_inicio) {
                $query->where(function ($q) use ($fecha_inicio) {
                    $q->whereDate('pfh.fecha_inicio_proceso', '>=', $fecha_inicio)
                      ->orWhereDate('pfh.fecha_inicio_periodo', '>=', $fecha_inicio)
                      ->orWhereDate('pfh.fecha_fin_periodo', '>=', $fecha_inicio);
                });
            } elseif ($fecha_fin) {
                $query->where(function ($q) use ($fecha_fin) {
                    $q->whereDate('pfh.fecha_inicio_proceso', '<=', $fecha_fin)
                      ->orWhereDate('pfh.fecha_inicio_periodo', '<=', $fecha_fin)
                      ->orWhereDate('pfh.fecha_fin_periodo', '<=', $fecha_fin);
                });
            }
            
            if (!empty($q)) {
                $searchTerm = strtolower($q);
                $query->where(function ($w) use ($searchTerm) {
                    $w->where(DB::raw('LOWER(pfh.descripcion)'), 'LIKE', "%{$searchTerm}%")
                      ->orWhere(DB::raw('LOWER(te.descripcion)'), 'LIKE', "%{$searchTerm}%")
                      ->orWhere(DB::raw('LOWER(p.nombre_razonsocial)'), 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // **OPCIÓN 1: Agrupar por proveedor y tipo (para casos como CHUMPITAZ RAMIREZ)**
            if ($agrupar) {
                $query->select(
                    'p.idpersona',
                    'p.numero_documento',
                    'p.nombre_razonsocial',
                    'te.idtipoestandarproveedor',
                    'te.descripcion as tipo_homologacion',
                    DB::raw('GROUP_CONCAT(DISTINCT docte.descripcion) as descripciones_docs'),
                    DB::raw('GROUP_CONCAT(DISTINCT d.archivo) as archivos'),
                    DB::raw('GROUP_CONCAT(DISTINCT d.iddocsproveedortipoestandar) as ids_documentos'),
                    DB::raw('MIN(pfh.fecha_inicio_proceso) as primera_fecha_proceso'),
                    DB::raw('MAX(pfh.fecha_inicio_proceso) as ultima_fecha_proceso')
                )->groupBy('p.idpersona', 'te.idtipoestandarproveedor');
            } else {
                // **OPCIÓN 2: Sin agrupar (documentos individuales)**
                $query->select(
                    'p.numero_documento',
                    'p.nombre_razonsocial',
                    'te.descripcion as tipo_homologacion',
                    'docte.descripcion as descripcion_doc',
                    'd.archivo',
                    'd.iddocsproveedortipoestandar',
                    'pfh.fecha_inicio_proceso'
                );
            }
            
            $documentos = $query->get();
            
            Log::info('Documentos encontrados: ' . $documentos->count() . ' (agrupados: ' . ($agrupar ? 'SI' : 'NO') . ')');
            
            if ($documentos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay documentos para descargar con los filtros aplicados.'
                ], 404);
            }
            
            // Crear ZIP temporal
            $tempDir = sys_get_temp_dir();
            $zipFilename = 'homologaciones_' . now()->format('Ymd_His') . '.zip';
            $tempZipPath = $tempDir . '/' . $zipFilename;
            
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('No se pudo crear el archivo ZIP');
            }
            
            $archivosAgregados = 0;
            $archivosNoEncontrados = [];
            
            foreach ($documentos as $doc) {
                if ($agrupar) {
                    // **Procesar documentos agrupados**
                    $archivosArray = explode(',', $doc->archivos);
                    $idsArray = explode(',', $doc->ids_documentos);
                    $descripcionesArray = explode(',', $doc->descripciones_docs);
                    
                    for ($i = 0; $i < count($archivosArray); $i++) {
                        $archivo = trim($archivosArray[$i]);
                        if (empty($archivo)) continue;
                        
                        $rutaArchivo = public_path($archivo);
                        
                        if (!file_exists($rutaArchivo)) {
                            $archivosNoEncontrados[] = 'ID ' . ($idsArray[$i] ?? 'N/A') . ': ' . $archivo;
                            continue;
                        }
                        
                        // Estructura de carpetas para agrupados
                        $proveedor = Str::slug($doc->numero_documento . '_' . $doc->nombre_razonsocial);
                        $tipo = Str::slug($doc->tipo_homologacion);
                        $extension = pathinfo($rutaArchivo, PATHINFO_EXTENSION);
                        $descripcion = $descripcionesArray[$i] ?? 'documento';
                        $nombreArchivo = Str::slug($descripcion) . '_' . 
                                         ($idsArray[$i] ?? 'doc') . '.' . $extension;
                        
                        $rutaEnZip = $proveedor . '/' . $tipo . '/' . $nombreArchivo;
                        
                        if ($zip->addFile($rutaArchivo, $rutaEnZip)) {
                            $archivosAgregados++;
                        }
                    }
                } else {
                    // **Procesar documentos individuales**
                    if (empty($doc->archivo)) {
                        $archivosNoEncontrados[] = 'ID ' . $doc->iddocsproveedortipoestandar . ': Sin ruta';
                        continue;
                    }
                    
                    $rutaArchivo = public_path($doc->archivo);
                    
                    if (!file_exists($rutaArchivo)) {
                        $archivosNoEncontrados[] = 'ID ' . $doc->iddocsproveedortipoestandar . ': ' . $doc->archivo;
                        continue;
                    }
                    
                    // Estructura de carpetas para individuales
                    $proveedor = Str::slug($doc->numero_documento . '_' . $doc->nombre_razonsocial);
                    $tipo = Str::slug($doc->tipo_homologacion);
                    $extension = pathinfo($rutaArchivo, PATHINFO_EXTENSION);
                    $nombreArchivo = Str::slug($doc->descripcion_doc) . '_' . 
                                     $doc->iddocsproveedortipoestandar . '.' . $extension;
                    
                    $rutaEnZip = $proveedor . '/' . $tipo . '/' . $nombreArchivo;
                    
                    if ($zip->addFile($rutaArchivo, $rutaEnZip)) {
                        $archivosAgregados++;
                    }
                }
            }
            
            $zip->close();
            
            if ($archivosAgregados === 0) {
                if (file_exists($tempZipPath)) {
                    unlink($tempZipPath);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron archivos válidos para descargar.',
                    'detalles' => $archivosNoEncontrados
                ], 404);
            }
            
            Log::info('ZIP creado exitosamente', [
                'archivos_agregados' => $archivosAgregados,
                'agrupado' => $agrupar ? 'SI' : 'NO'
            ]);
            
            // Preparar respuesta
            return response()->download($tempZipPath, $zipFilename, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFilename . '"',
                'Content-Length' => filesize($tempZipPath),
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error('Error en descarga masiva: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . (env('APP_DEBUG') ? $e->getMessage() : 'Contacte al administrador')
            ], 500);
        }
    }
    
    /**
     * Nuevo método: Obtener proveedores agrupados para el filtro
     */
    public function obtenerProveedoresAgrupados(Request $request)
    {
        try {
            $proveedores = DB::table('persona_facha_homologacion as pfh')
                ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1')
                ->select(
                    'p.idpersona',
                    'p.numero_documento',
                    'p.nombre_razonsocial',
                    DB::raw('COUNT(pfh.idpersona_facha_homologacion) as total_homologaciones')
                )
                ->groupBy('p.idpersona', 'p.numero_documento', 'p.nombre_razonsocial')
                ->orderBy('p.nombre_razonsocial')
                ->get();
            
            return response()->json([
                'success' => true,
                'proveedores' => $proveedores
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Método para verificar archivos de un proveedor específico
     */
    public function verificarArchivos(Request $request)
    {
        try {
            $id_proveedor = $request->input('id_proveedor');
            $agrupar = $request->input('agrupar', true);
            
            $query = DB::table('docsproveedortipoestandar as d')
                ->join('persona_facha_homologacion as pfh', 'pfh.idpersona_facha_homologacion', '=', 'd.idpersona_facha_homologacion')
                ->join('detalletipoestandarproveedor as dte', 'dte.iddetalletipoestandarproveedor', '=', 'd.iddetalletipoestandarproveedor')
                ->join('tipoestandarproveedor as te', 'te.idtipoestandarproveedor', '=', 'dte.idtipoestandarproveedor')
                ->join('persona as p', 'p.idpersona', '=', 'pfh.idpersona')
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1');
            
            if ($id_proveedor) {
                $query->where('pfh.idpersona', $id_proveedor);
            }
            
            if ($agrupar) {
                $documentos = $query->select(
                    'p.idpersona',
                    'p.nombre_razonsocial',
                    'te.descripcion as tipo_homologacion',
                    DB::raw('GROUP_CONCAT(DISTINCT d.archivo) as archivos'),
                    DB::raw('COUNT(DISTINCT d.iddocsproveedortipoestandar) as total_documentos'),
                    DB::raw('SUM(CASE WHEN d.archivo IS NOT NULL AND d.archivo != "" THEN 1 ELSE 0 END) as con_ruta')
                )->groupBy('p.idpersona', 'te.descripcion')->get();
            } else {
                $documentos = $query->select(
                    'd.iddocsproveedortipoestandar',
                    'd.archivo',
                    'p.nombre_razonsocial',
                    'te.descripcion as tipo_homologacion',
                    'pfh.fecha_inicio_proceso'
                )->limit(20)->get();
            }
            
            return response()->json([
                'success' => true,
                'agrupado' => $agrupar,
                'data' => $documentos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Método de prueba
     */
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'Controlador de homologaciones funcionando',
            'funciones' => [
                'descargaMasiva' => 'Descarga con/sin agrupación',
                'verificarArchivos' => 'Verifica archivos agrupados',
                'obtenerProveedoresAgrupados' => 'Lista proveedores únicos'
            ]
        ]);
    }



}
