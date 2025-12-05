<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        if ($r->permisos && count($r->permisos) > 0) {

            foreach ($r->permisos as $permiso) {

                DB::table('usuario_permiso')->insert([
                    'users_id' => $user->id,
                    'idpermiso' => $permiso
                ]);

            }
        }



        return ApiResponse::success([
            'id' => 'nuevo'
        ], 'Usuario creado correctamente');

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
  public function select2pers_sin_user()
  {
    try {
      $data  = User::personas_sin_usuario();

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

  






