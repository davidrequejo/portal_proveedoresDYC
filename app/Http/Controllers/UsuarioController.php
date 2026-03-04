<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB; 
use App\Models\Permiso;
use App\Helpers\ApiResponse;


class UsuarioController extends Controller{

  public function index(Request $req)
  {        
      return view('usuario');
  }

  public function crear_usuario(Request $r)
  {
    try {

        // 1. Validación
        $data = $r->validate([
            'idpersona'   => 'required|integer',
            'tipoPersona' => 'required|string',
            'email'       => 'required|string|unique:users,email',
            'password'    => 'required|string|min:8',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'integer'
        ]);

        // 2. Crear usuario
        $user = User::create([
            'idpersona' => $r->idpersona,
            'name'      => $r->tipoPersona,   // lo colocamos en name
            'email'     => $r->email,
            'password'  => bcrypt($r->password)
            //'rol'       => $r->tipoPersona // si tu tabla tiene el campo "rol"
        ]);

        // 3. Asignar permisos si existen
        /*if ($r->permisos) {
            $user->syncPermissions($r->permisos);
        }*/
        // 3. Registrar permisos en tabla intermedia
        /*if ($r->permisos && count($r->permisos) > 0) {

            foreach ($r->permisos as $permiso) {

                DB::table('usuario_permiso')->insert([
                    'users_id' => $user->id,
                    'idpermiso' => $permiso
                ]);

            }
        }*/

        // 3. Registrar permisos en tabla intermedia si el usuario fue creado PROVEEDOR,  CLIENTE, COMPRADOR
        $permisosPorRol = [
            'ADMINISTRADOR' => [1, 2, 3, 4, 5, 6, 7, 10],
            'PROVEEDOR'     => [8, 9],
            'CLIENTE'       => [11],
            'COMPRADOR'     => [7,10],
        ];
        
        $permisos = $permisosPorRol[$r->tipoPersona] ?? [];

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



        return ApiResponse::success([
            'id' => 'nuevo'
        ], 'Usuario creado correctamente');

    } catch (\Throwable $e) {
     return ApiResponse::error($e);
    }
  }

  public function mostrar_usuario_editar($id)
  {
      try {

          // 1) Usuario
          $user = User::findOrFail($id);

          // 2) Permisos asignados del usuario (solo ids)
          $permisos_usuario = DB::table('usuario_permiso')
          ->where('users_id', $user->id)
          ->pluck('idpermiso')
          ->map(fn($x) => (int)$x)
          ->values();

          return ApiResponse::success([
          'user' => [
              'id'        => $user->id,
              'idpersona' => $user->idpersona,
              'tipoPersona' => $user->name,   // tú lo guardas en name
              'email'     => $user->email,
          ],
          'permisos_usuario' => $permisos_usuario
          ], 'Datos para editar usuario');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }
  }

  public function editar_usuario(Request $r)
  {
      try {

          // 1️⃣ Validación
          $data = $r->validate([
              'id'          => 'required|integer|exists:users,id',
              'idpersona'   => 'required|integer',
              'tipoPersona' => 'required|string',
              'email'       => [ 'required', Rule::unique('users','email')->ignore($r->id, 'id'), ],
              'password'    => 'nullable|string|min:8',
              'permisos'    => 'nullable|array',
              'permisos.*'  => 'integer'
          ]);

          // 2️⃣ Buscar usuario
          $user = User::findOrFail($r->id);

          // 3️⃣ Actualizar datos básicos
          $user->update([
              'idpersona' => $r->idpersona,
              'name'      => $r->tipoPersona,
              'email'     => $r->email,
          ]);

          // 4️⃣ Actualizar contraseña SOLO si se envía
          if (!empty($r->password)) {
              $user->update([
                  'password' => bcrypt($r->password)
              ]);
          }

          // 5️⃣ SINCRONIZAR permisos (CLAVE 🔥)
          DB::table('usuario_permiso')
              ->where('users_id', $user->id)
              ->delete();

          if ($r->permisos && count($r->permisos) > 0) {
              foreach ($r->permisos as $permiso) {
                  DB::table('usuario_permiso')->insert([
                      'users_id' => $user->id,
                      'idpermiso' => $permiso
                  ]);
              }
          }

          return ApiResponse::success([
              'id' => $user->id
          ], 'Usuario actualizado correctamente');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }
  }

  public function eliminar_usuario(Request $r, int $id)
  {
    try {
        $usuario = User::findOrFail($id);

        // Actualizamos únicamente el estado a 0
        $usuario->update([
            'estado_trash' => 0
        ]);

        return ApiResponse::success([
            'id_usuario' => $usuario->id,
            'estado_trash' => 0
        ], 'Eliminado correctamente');
        
    } catch (\Throwable $e) {
        return ApiResponse::error($e);
    }
  }

  public function Listar_usuarios(Request $r)
  {
      // Parámetros de entrada del request
      $perPage = (int) $r->input('per_page', 20);           // Número de elementos por página (por defecto 20)
      $page    = (int) $r->input('page', 1);                // Página actual (por defecto 1)
      $sort    = $r->input('sort', 'id');                   // Columna a ordenar (por defecto 'idpersona')
      $dir     = $r->input('dir', 'asc');                   // Dirección de orden ('asc' o 'desc')
      $q       = trim($r->input('q', ''));                  // Término de búsqueda global    


      // Columnas válidas para ordenar
      $validSorts = [ 'id', 'idpersona', 'usuario','nombre_razonsocial', 'tipo_documento', 'numero_documento', 'celular','tipo_entidad_sunat','estado_trash'
      ];

      // Si la columna para ordenar no es válida, usamos 'idpersona'
      if (!in_array($sort, $validSorts, true)) {
          $sort = 'idpersona';
      }

      // Asegurarse de que la dirección de orden sea 'asc' o 'desc'
      $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

      // Crear la consulta base
      $query = DB::table('users AS u')
          ->join('persona as p', 'u.idpersona', '=', 'p.idpersona')
          ->join('tipo_persona AS tp', 'tp.idtipo_persona', '=', 'p.idtipo_persona')
          ->join('sunat_c06_doc_identidad as doc', 'p.tipo_documento', '=', 'doc.code_sunat')
          ->select(
              'u.id', 'p.idpersona', DB::raw('u.email AS usuario'), 'u.password', 'p.nombre_razonsocial',
              'doc.abreviatura', 'p.numero_documento', 'p.tipo_entidad_sunat', DB::raw('tp.descripcion AS tipo_persona'),
              'u.estado_trash',

          )
          ->where('u.estado_trash', '1')          // Activos
          ->where('u.estado_delete', '1');  // No eliminados

              // Si hay un término de búsqueda, lo aplicamos
              if ($q !== '') {
                  $query->where(function ($w) use ($q) {
                      $q = strtolower($q);

                      $w->whereRaw("LOWER(p.nombre_razonsocial) LIKE ?", ["%{$q}%"])
                          ->orWhereRaw("LOWER(p.numero_documento) LIKE ?", ["%{$q}%"])
                          ->orWhereRaw("LOWER(p.tipo_entidad_sunat) LIKE ?", ["%{$q}%"])
                          ->orWhereRaw("LOWER(doc.abreviatura) LIKE ?", ["%{$q}%"])
                          ->orWhereRaw("LOWER(tp.descripcion) LIKE ?", ["%{$q}%"])
                          ->orWhereRaw("LOWER(u.email) LIKE ?", ["%{$q}%"])
                          ->orWhereRaw("LOWER(u.estado_trash) LIKE ?", ["%{$q}%"]);
                  });
              }

      // Ordenar
      $query->orderBy($sort, $dir);

      // Paginación
      $usuarios = $query->paginate($perPage, ['*'], 'page', $page);

      // Transformar salida
      $usuarios->getCollection()->transform(function ($usuarios) {
          return [
              'id'                 => $usuarios->id,
              'idpersona'          => $usuarios->idpersona,
              'usuario'            => $usuarios->usuario,
              'nombre_razonsocial' => $usuarios->nombre_razonsocial,
              'abreviatura'        => $usuarios->abreviatura,
              'numero_documento'   => $usuarios->numero_documento,
              'tipo_entidad_sunat' => $usuarios->tipo_entidad_sunat,
              'tipo_persona'       => $usuarios->tipo_persona,
              'estado_trash'       => $usuarios->estado_trash,
          ];
      });

      // Respuesta JSON
      return response()->json([
          'data'         => $usuarios->items(),
          'current_page' => $usuarios->currentPage(),
          'per_page'     => $usuarios->perPage(),
          'total'        => $usuarios->total(),
          'last_page'    => $usuarios->lastPage(),
          'from'         => $usuarios->firstItem(),
          'to'           => $usuarios->lastItem(),
          'sort'         => $sort,
          'dir'          => $dir,
          'q'            => $q,
      ]);
  }

  public function  MostrarPermisos_crear()
  {
      $permisos = Permiso::orderBy('grupo')->orderBy('escenario')->get();

      $grupos = [];

      foreach ($permisos as $p) {
          $grupo = $p->grupo;

          if (!isset($grupos[$grupo])) {
              $grupos[$grupo] = [];
          }

          $grupos[$grupo][] = [
              'idpermiso' => $p->idpermiso,
              'icono'     => $p->icono,
              'escenario' => $p->escenario
          ];
      }

      return ApiResponse::success($grupos, 'Lista de Permisos');
  }

    // Método para obtener todos roles personas
  public function select2pers_sin_user(Request $request)
  {
    try {
        // 👉 idpersona solo llegará cuando sea edición
        $idpersona = $request->input('idpersona');

        $data = User::personas_sin_usuario($idpersona);

      $options = ''; // string para concatenar HTML
      foreach ($data as $t) {
          $options .= '<option value="'.$t->idpersona.'" data-rol="'.$t->Rolpersona.'">' . e($t->nombre_razonsocial). ' '. e($t->numero_documento). '</option>';
      }

      return ApiResponse::success($options, 'Lista de personas obtenidas');

    } catch (\Throwable $e) {
        return ApiResponse::error($e);
    }

  }


}

  






