<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Mail;
use App\Mail\CredencialesClientesMail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class ClienteController extends Controller
{
    public function index(Request $req)
    {        
        return view('cliente');
    }

    public function crear_cliente(Request  $request)
    {
       try {
          // Validar los datos del formulario
          $rules  = [
            'idtipo_persona'                 => 'required|integer',
            'tipo_entidad_sunat'             => 'required',
            'tipo_documento'                 => 'required',
            'numero_documento'               => 'required|string|unique:persona,numero_documento',
            'nombre_razonsocial'             => 'required|string|max:255',
            'nombre_persona_natural'         => 'nullable|string|max:255',
            'apellido_paterno_per_natural'   => 'nullable|string|max:255',
            'apellido_materno_per_natural'   => 'nullable|string|max:255',
            'celular'                        => 'required|string|max:15',
            'direccion'                      => 'required|string|max:255',
            'email'                          => 'required|email|unique:persona,email',
            'distrito'                       => 'nullable',
            'provincia'                      => 'nullable',
            'departamento'                   => 'nullable',
            'usuario_portal'                 => 'nullable|string|max:255',
            'clave_portal'                   => 'nullable|string|min:6'
          ];

          //$validated = $request->validate($rules);
          $validator = Validator::make( $request->all(), $rules );

              // Aplica la regla 'unique' solo si usuario_portal no está vacío
          $validator->sometimes('usuario_portal', 'unique:users,email', function ($input) {
              return !empty($input->usuario_portal);
          });

          // Aplica 'required' a clave_portal solo si usuario_portal no está vacío
          $validator->sometimes('clave_portal', 'required', function ($input) {
              return !empty($input->usuario_portal);
          });

          if ($validator->fails()) {
              // Usar tu clase ApiResponse para retornar errores
              return ApiResponse::validation(
                  $validator->errors()->toArray(),
                  'Campos por rellenar correctamente'
              );
          }
          
          $data = $validator->validated();

          // 1. Crear  cliente
          $createCliente = Cliente::create($data);


          // 2. Crear usuario para el cliente solo si los campos no son vacíos
          if (!empty($data['usuario_portal']) && !empty($data['clave_portal'])) {

                $user = User::create([
                    'idpersona' => $createCliente->idpersona,
                    'name'      => 'CLIENTE',
                    'email'     => $data['usuario_portal'],
                    'password'  => bcrypt($data['clave_portal']),
                ]);

              // 3. Registrar permisos en tabla intermedia si el usuario fue creado
               $permisos = [11];

              if ($user->id) {
                  $u_permisos = [];

                  foreach ($permisos as $permiso) {
                      $u_permisos[] = [
                          'users_id' => $user->id,
                          'idpermiso' => $permiso,
                      ];
                  }

                  DB::table('usuario_permiso')->insert($u_permisos);
              }

              // 4. Enviar correo con credenciales al proveedor
                // 4.1 obtenener nombre y correo del soporte desde el usuario autenticado
                $nombreSoporte = auth()->user()->persona?->nombre_razonsocial;
                $correoSoporte = auth()->user()->persona?->email;
              try {
                  Mail::to($createCliente->email)->queue(
                      new CredencialesClientesMail(
                          nombre: $createCliente->nombre_razonsocial,
                          usuario: $data['usuario_portal'],
                          clave: $data['clave_portal'],
                          nombreSoporte: $nombreSoporte,
                          correoSoporte: $correoSoporte
                      )
                  );
              } catch (\Throwable $e) {
                  // opcional: log
                  \Log::error("Error enviando correo Cliente: " . $e->getMessage());
              }

          }

          return ApiResponse::success([
                'idpersona' => $createCliente->idpersona
            ], 'Cliente creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_cliente(Request $r, $idpersona)
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

            // 2. Actualizar cliente
            $cliente = Cliente::where('idpersona', $idpersona)->firstOrFail();
            $cliente->update($data);

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
                      new CredencialesClientesMail(
                          nombre: $r->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal,
                          nombreSoporte: $nombreSoporte,
                          correoSoporte: $correoSoporte
                      )
                  );
              } catch (\Throwable $e) {
                  // opcional: log
                  \Log::error("Error enviando correo Cliente: " . $e->getMessage());
              }

            } else {
              // Si el ID no fue proporcionado, creamos un nuevo usuario
              $user = User::create([
                  'idpersona' => $cliente->idpersona,
                  'name'      => 'CLIENTE', 
                  'email'     => $r->usuario_portal,
                  'password'  => bcrypt($r->clave_portal) // Solo si se proporciona la clave
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

              // 4. Enviar correo con credenciales al cliente
              try {
                  Mail::to($r->email)->queue(
                      new CredencialesClientesMail(
                          nombre: $r->nombre_razonsocial,
                          usuario: $r->usuario_portal,
                          clave: $r->clave_portal,
                          nombreSoporte: $nombreSoporte,
                          correoSoporte: $correoSoporte
                      )
                  );
              } catch (\Throwable $e) {
                  // opcional: log
                  \Log::error("Error enviando correo Cliente: " . $e->getMessage());
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

            // 1. Desactivar cliente
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

    public function Listar_clientes(Request $r)
    {
        // Parámetros de entrada
        $perPage = (int) $r->input('per_page', 20);
        $page    = (int) $r->input('page', 1);
        $sort    = $r->input('sort', 'idpersona');
        $dir     = $r->input('dir', 'asc');
        $q       = trim($r->input('q', ''));

        // Filtros específicos
        $estado_actualizacion = $r->input('estado_actualizaciondatos_filtro'); // 0,1,2
        $tipo_entidad_sunat   = $r->input('tipo_entidad_sunat');              // texto

        // Columnas válidas para ordenar
        $validSorts = [
            'idpersona', 'nombre_razonsocial', 'apellidos_nombrecomercial', 'tipo_documento',
            'numero_documento', 'celular', 'direccion', 'distrito', 'provincia', 'departamento',
            'email', 'tipo_entidad_sunat', 'estado'
        ];

        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idpersona';
        }
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        /* ====================== QUERY BASE ====================== */
        $query = DB::table('persona as p')
            ->join('tipo_persona as tp', 'p.idtipo_persona', '=', 'tp.idtipo_persona')
            ->join('sunat_c06_doc_identidad as doc', 'p.tipo_documento', '=', 'doc.code_sunat')
            ->select(
                'p.idpersona',
                'p.codigo_s10',
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
                'p.estado',
                DB::raw('(
                    SELECT CASE
                        WHEN p.codigo_s10 IS NULL OR p.codigo_s10 = \'\' THEN 0
                        -- 🟥 Existe y tiene pendientes
                        WHEN EXISTS (
                            SELECT 1
                            FROM logbd lbd
                            WHERE lbd.nombre_tabla = \'persona\'
                            AND lbd.id_registrotabla = p.idpersona
                            AND lbd.estado_sincronizacions10 = 0
                        ) THEN 1

                        -- 🟦 Existe pero todo está sincronizado
                        -- ⚪ No existe ningún registro
                        WHEN NOT EXISTS (
                            SELECT 1
                            FROM persona_cuentabancaria pcb
                            WHERE pcb.idpersona = p.idpersona
                            AND pcb.estado_trash = 1
                            AND pcb.estado_delete = 1
                        ) THEN 1
                        WHEN EXISTS (
                            SELECT 1
                            FROM persona_cuentabancaria pcb
                            WHERE pcb.idpersona = p.idpersona
                            AND pcb.estado_trash = 1
                            AND pcb.estado_delete = 1
                            AND (
                                pcb.NroIdentificadorCuentaBancos10 IS NULL
                                OR pcb.NroIdentificadorCuentaBancos10 = \'\'
                                OR EXISTS (
                                    SELECT 1
                                    FROM logbd lbd
                                    WHERE lbd.nombre_tabla = \'persona_cuentabancaria\'
                                    AND lbd.id_registrotabla = pcb.idpersona_cuentabancaria
                                    AND lbd.idpersona = p.idpersona
                                    AND lbd.estado_sincronizacions10 = 0
                                )
                            )
                        ) THEN 1
                        ELSE 2
                    END
                ) AS estado_sincronizacion')
            )
            ->where('p.estado', 1)
            ->where('p.estado_delete', 1)
            ->where('p.idtipo_persona', 5); // Solo clientes


        /* ====================== FILTROS DIRECTOS (WHERE) ====================== */
        if ($r->filled('tipo_entidad_sunat')) { $query->where('p.tipo_entidad_sunat', $tipo_entidad_sunat); }

        /* ====================== BÚSQUEDA GLOBAL ====================== */
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

        /* ====================== GROUP BY ====================== */
        // Agrupamos por todas las columnas de persona (excepto los campos calculados)
        // para poder usar los alias en el HAVING sin problemas de ambigüedad.
        $query->groupBy(
            'p.idpersona',
            'p.codigo_s10',
            'tp.descripcion',          // tipoPersona
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

        if ($r->filled('estado_actualizaciondatos_filtro')) {
            $query->having('estado_sincronizacion', '=', (int) $estado_actualizacion);
        }

        /* ====================== ORDEN Y PAGINACIÓN ====================== */
        $query->orderBy($sort, $dir);

        // Usamos paginate() directamente; Laravel se encarga de contar correctamente
        // respetando los HAVING (el count se aplica sobre la consulta completa con los filtros)
        $clientes = $query->paginate($perPage, ['*'], 'page', $page);

        /* ====================== TRANSFORMACIÓN DE RESULTADOS ====================== */
        $clientes->getCollection()->transform(function ($cliente) {
            return [
                'idpersona'                 => $cliente->idpersona,
                'codigo_s10'                 => $cliente->codigo_s10,
                'tipoPersona'                 => $cliente->tipoPersona,
                'tipo_documento'              => $cliente->tipo_documento,
                'nombre_razonsocial'          => $cliente->nombre_razonsocial,
                'apellidos_nombrecomercial'   => $cliente->apellidos_nombrecomercial,
                'abreviatura'                 => $cliente->abreviatura,
                'numero_documento'             => $cliente->numero_documento,
                'celular'                      => $cliente->celular,
                'direccion'                    => $cliente->direccion,
                'distrito'                     => $cliente->distrito,
                'provincia'                    => $cliente->provincia,
                'departamento'                  => $cliente->departamento,
                'email'                         => $cliente->email,
                'tipo_entidad_sunat'            => $cliente->tipo_entidad_sunat,
                'estado'                        => $cliente->estado,
                'estado_sincronizacion'         => (int) $cliente->estado_sincronizacion,
            ];
        });

        /* ====================== RESPUESTA JSON ====================== */
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

    public function ImportarProveedoresExcel(Request $request) {
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
            // 2️⃣ Leer Excel
            // ===============================
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray('', false, false, false);
            $registros = [];
            $contador = 0;

            // Obtener datos del soporte autenticado para el correo
            $soporte = auth()->user();
            $nombreSoporte = $soporte->persona?->nombre_razonsocial ?? 'Soporte';
            $correoSoporte = $soporte->persona?->email ?? config('mail.from.address');

            // ===============================
            // 3️⃣ Recorrer filas y construir array de inserción
            // ===============================
            foreach ($rows as $i => $row) {
                if ($i === 0) continue; // saltar encabezado

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

                // Validaciones mínimas
                if (!$tipoEntidadSunat || !$tipoDocumento || !$numeroDocumento || !$razonSocial) {
                    continue;
                }
                if (strtoupper($tipoEntidadSunat) === 'NATURAL') {
                    if (!$nombres || !$apePaterno) {
                        continue;
                    }
                }

                $registros[] = [
                    'idtipo_persona'               => 5,
                    'tipo_entidad_sunat'           => $tipoEntidadSunat,
                    'tipo_documento'                => $tipoDocumento,
                    'numero_documento'               => $numeroDocumento,
                    'nombre_razonsocial'             => $razonSocial,
                    'nombre_persona_natural'         => $nombres ?: null,
                    'apellido_paterno_per_natural'   => $apePaterno ?: null,
                    'apellido_materno_per_natural'   => $apeMaterno ?: null,
                    'celular'                         => $telefono ?: null,
                    'direccion'                       => $direccion ?: null,
                    'email'                           => $email ?: null,
                    'created_at'                      => now(),
                    'updated_at'                      => now(),
                ];
                $contador++;
            }

            if (count($registros) === 0) {
                return ApiResponse::validation([], 'El Excel no contiene datos válidos');
            }

            // ===============================
            // 4️⃣ Transacción: insertar clientes y crear usuarios
            // ===============================
            DB::beginTransaction();

            try {
                $clientesCreados = [];

                // Insertar clientes en chunks
                foreach (array_chunk($registros, 300) as $chunk) {
                    foreach ($chunk as $item) {
                        $clientesCreados[] = Cliente::create($item);
                    }
                }

                // Crear usuarios para los clientes que tienen email
                foreach ($clientesCreados as $cliente) {
                    if (!empty($cliente->email)) {
                        // Generar nombre de usuario basado en la razón social y tipo de entidad
                        $nombreUsuario = $this->generarNombreUsuario($cliente->nombre_razonsocial, $cliente->tipo_entidad_sunat);
                        $claveAleatoria = Str::random(12);

                        // Crear usuario
                        $user = User::create([
                            'idpersona' => $cliente->idpersona,
                            'name'      => substr($cliente->nombre_razonsocial, 0, 50) ?: 'Cliente',
                            'email'     => $nombreUsuario,
                            'password'  => Hash::make($claveAleatoria),
                        ]);

                        // Asignar permisos fijos (8 y 9)
                        $permisos = [8, 9];
                        $u_permisos = [];
                        foreach ($permisos as $permiso) {
                            $u_permisos[] = [
                                'users_id'  => $user->id,
                                'idpermiso' => $permiso,
                            ];
                        }
                        DB::table('usuario_permiso')->insert($u_permisos);

                        // Enviar correo con credenciales al email real del cliente
                        try {
                            Mail::to($cliente->email)->queue(
                                new CredencialesClientesMail(
                                    nombre: $cliente->nombre_razonsocial,
                                    usuario: $nombreUsuario,
                                    clave: $claveAleatoria,
                                    nombreSoporte: $nombreSoporte,
                                    correoSoporte: $correoSoporte
                                )
                            );
                        } catch (\Throwable $e) {
                            \Log::error("Error enviando correo a cliente ID {$cliente->idpersona}: " . $e->getMessage());
                        }
                    }
                }

                DB::commit();

                return ApiResponse::success(
                    null,
                    "Importación exitosa: {$contador} clientes"
                );

            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    /**
     * Genera un nombre de usuario único a partir de la razón social y el tipo de entidad.
     *
     * @param string $razonSocial
     * @param string|null $tipoEntidad (NATURAL o JURIDICA)
     * @return string
     */
    private function generarNombreUsuario($razonSocial, $tipoEntidad = null)
    {
        // Normalizar: eliminar tildes, convertir a minúsculas, quitar caracteres no alfanuméricos excepto espacios
        $razonSocial = strtr($razonSocial, 'áéíóúñ', 'aeioun');
        $razonSocial = preg_replace('/[^a-z0-9\s]/i', '', $razonSocial);
        $razonSocial = strtolower($razonSocial);
        
        // Dividir en palabras
        $palabras = array_filter(explode(' ', $razonSocial));
        
        // Lista de palabras vacías a ignorar (artículos, preposiciones, etc.)
        $stopWords = ['de', 'del', 'la', 'las', 'el', 'los', 'y', 'e', 'a', '&', 's.a.', 's.r.l.', 'e.i.r.l.', 'sac', 'srl', 'ltda', 'sa', 'eirl'];
        
        // Filtrar palabras vacías
        $palabras = array_filter($palabras, function($palabra) use ($stopWords) {
            return !in_array($palabra, $stopWords);
        });
        
        // Reindexar
        $palabras = array_values($palabras);
        
        if (empty($palabras)) {
            return 'cliente' . rand(100, 999);
        }
        
        $primera = $palabras[0];
        $segunda = $palabras[1] ?? null;
        
        // Si hay segunda palabra
        if ($segunda) {
            // Determinar si la primera es corta (longitud <= 3)
            if (strlen($primera) <= 3) {
                // Primera corta: tomar primera letra (mayúscula) + segunda completa
                $usuario = strtoupper(substr($primera, 0, 1)) . $segunda;
            } else {
                // Primera larga: si es persona natural, tomar dos letras de la segunda
                if ($tipoEntidad === 'NATURAL') {
                    $letrasSegunda = strtoupper(substr($segunda, 0, 1)) . substr($segunda, 1, 1);
                    $usuario = $primera . $letrasSegunda;
                } else {
                    $usuario = $primera . strtoupper(substr($segunda, 0, 1));
                }
            }
        } else {
            // Solo una palabra
            $usuario = $primera;
        }
        
        // Limitar longitud a 30 caracteres (por si acaso)
        $usuario = substr($usuario, 0, 30);
        
        // Asegurar que no esté vacío
        if (empty($usuario)) {
            $usuario = 'cliente' . rand(100, 999);
        }
        
        // Verificar unicidad en la tabla users (campo email)
        $original = $usuario;
        $contador = 1;
        while (User::where('email', $usuario)->exists()) {
            $usuario = $original . $contador;
            $contador++;
            // Limitar longitud para no exceder el máximo de la columna (por ejemplo 50)
            if (strlen($usuario) > 50) {
                $usuario = substr($original, 0, 45) . $contador;
            }
        }
        
        return $usuario;
    }



}
