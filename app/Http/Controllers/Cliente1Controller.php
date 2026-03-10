<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Mail;
use App\Mail\CredencialesProveedorMail;
use PhpOffice\PhpSpreadsheet\IOFactory;


class ClienteController extends Controller
{
    public function index(Request $req)
    {        
        return view('cliente');
    }

    public function crear_cliente(Request $r)
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
          $cliente = Cliente::create($data);

          // 2. Crear usuario para el proveedor
          /*$user = User::create([
              'idpersona' =>  $cliente->idpersona,
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
                  'idpersona' => $cliente->idpersona,
                  'name'      => 'Cliente', 
                  'email'     => $r->usuario_portal,
                  'password'  => bcrypt($r->clave_portal),
              ]);

              // 3. Registrar permisos en tabla intermedia si el usuario fue creado
              $permisos = [11];

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
                  Mail::to($cliente->email)->send(
                      new CredencialesProveedorMail(
                          nombre: $cliente->nombre_razonsocial,
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
                'idpersona' => $cliente->idpersona
            ], 'Proveedor creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_cliente(Request $r, $idpersona)
    {
        try {

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

            // 2. Actualizar cliente
            $cliente = Cliente::where('idpersona', $idpersona)->firstOrFail();
            $cliente->update($data);

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
                  'idpersona' => $cliente->idpersona,
                  'name'      => 'Cliente', 
                  'email'     => $r->usuario_portal,
                  'password'  => bcrypt($r->clave_portal) // Solo si se proporciona la clave
              ]);

              // 3. Registrar permisos en tabla intermedia si el usuario fue creado
              if ($user->id) {
                  DB::table('usuario_permiso')->insert([
                      'users_id' => $user->id,
                      'idpermiso' => '11',
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
                  \Log::error("Error enviando correo cliente: " . $e->getMessage());
              }
            }



            return ApiResponse::success([
                'idpersona' => $cliente->idpersona,
                'users_id'  => $user->id
            ], 'Cliente actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function mostrar_cliente($idpersona)
    {
        try {

            // 1. Buscar cliente por idpersona
            $cliente = Cliente::where('idpersona', $idpersona)->firstOrFail();

            // 2. Buscar usuario asociado (opcional, si editas usuario)
            $usuario = User::where('idpersona', $idpersona)->first();

            if (!$usuario) { $usuario = null; }

            return ApiResponse::success([
                'cliente' => $cliente,
                'usuario'   => $usuario
            ], 'Cliente encontrado');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function eliminar_cliente($idpersona)
    {
        DB::beginTransaction();

        try {

            // 1. Desactivar Cliente
            $cliente = Cliente::where('idpersona', $idpersona)->firstOrFail();
            $cliente->estado = 0;
            $cliente->save();

            // 2. Desactivar usuario asociado
            $user = User::where('idpersona', $idpersona)->first();

            if ($user) {
                $user->estado_trash = 0;
                $user->save();
            }

            DB::commit();

            return ApiResponse::success([
                'idpersona' => $idpersona
            ], 'Cliente y usuario desactivados correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error($e);
        }
    }

    public function ImportarclientesExcel(Request $request)
    {
        try {

            // ===============================
            // 1️⃣ Validar archivo
            // ===============================
            if (!$request->hasFile('file_excel_cliente_masivo')) {
                return ApiResponse::validation([], 'Debe seleccionar un archivo Excel');
            }

            $file = $request->file('file_excel_cliente_masivo');
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
                    'idtipo_persona'               => 5, // fijo
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
                    Cliente::create($item);
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




    public function Listar_clientes(Request $r)
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
            ->select(
                'p.idpersona',
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
                'p.estado'
            )

            ->where('p.estado', '1')
            ->where('p.estado_delete', '1')
            ->where('p.idtipo_persona', '5')

            // 🔑 CLAVE: agrupar por persona
            ->groupBy(
                'p.idpersona',
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
        $clientes = $query->paginate($perPage, ['*'], 'page', $page);

        // Formatear los resultados antes de devolverlos
        $clientes->getCollection()->transform(function ($cliente) {
            return [
                'idpersona'             => $cliente->idpersona,
                'tipoPersona'           => $cliente->tipoPersona,
                'tipo_documento'        => $cliente->tipo_documento,
                'nombre_razonsocial'    => $cliente->nombre_razonsocial,
                'apellidos_nombrecomercial' => $cliente->apellidos_nombrecomercial,
                'abreviatura'           => $cliente->abreviatura,
                'numero_documento'     => $cliente->numero_documento,
                'celular'               => $cliente->celular,
                'direccion'             => $cliente->direccion,
                'distrito'              => $cliente->distrito,
                'provincia'             => $cliente->provincia,
                'departamento'          => $cliente->departamento,
                'email'                 => $cliente->email,
                'tipo_entidad_sunat'    => $cliente->tipo_entidad_sunat,
                'estado'                => $cliente->estado,
            ];
        });

        // Devolver la respuesta JSON con los resultados
        return response()->json([
            'data'         => $clientes->items(),
            'current_page' => $clientes->currentPage(),
            'per_page'     => $clientes->perPage(),
            'total'        => $clientes->total(),
            'last_page'    => $clientes->lastPage(),
            'from'         => $clientes->firstItem(),
            'to'           => $clientes->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }


}
