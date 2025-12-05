<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Persona;
use Illuminate\Support\Facades\DB; 
use App\Models\Rolpersona;

class PersonaController extends Controller
{
    public function index(Request $req)
    {        
        return view('persona');
    }

    public function crear_persona(Request $r)
    {
       try {
          // Validar los datos del formulario
          $data = $r->validate([
              'idtipo_persona' => 'required|string',
              'idbanco' => 'required|string',
              'tipo_entidad_sunat' => 'required|string',
              'tipo_documento' => 'required|string',
              'numero_documento' => 'required|string',
              'nombre_razonsocial' => 'required|string|max:255',
              'celular' => 'required|string|max:15',
              'direccion' => 'required|string|max:255',
              'email' => 'required|email',
              'distrito' => 'required|string',
              'provincia' => 'required|string',
              'departamento' => 'required|string'
          ]);

          $createpersona = Persona::create($data);

            return ApiResponse::success([
                'idpersona' => $createpersona->idpersona
            ], 'persona creado correctamente');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_persona(Request  $r, int $idpersona)
    {
        try {
            $persona = Persona::findOrFail($idpersona);

            $data = $r->validate([
                'idtipo_persona' => 'required|string',
                'idbanco' => 'required|string',
                'tipo_entidad_sunat' => 'required|string',
                'tipo_documento' => 'required|string',
                'numero_documento' => 'required|string',
                'nombre_razonsocial' => 'required|string|max:255',
                'celular' => 'required|string|max:15',
                'direccion' => 'required|string|max:255',
                'email' => 'required|email',
                'distrito' => 'required|string',
                'provincia' => 'required|string',
                'departamento' => 'required|string'
            ]);

            $persona->update($data);

            return ApiResponse::success([
                'idpersona' => $persona->idpersona
            ], 'persona actualizada correctamente');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    
    }

    public function eliminar_persona(Request $r, int $idpersona)
    {
        try {
            $persona = Persona::findOrFail($idpersona);

            // Actualizamos únicamente el estado a 0
            $persona->update([
                'estado' => 0
            ]);

            return ApiResponse::success([
                'idpersona' => $persona->idpersona,
                'estado' => 0
            ], 'Eliminado correctamente');
            
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function Listar_personas(Request $r)
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
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

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
            ->where('p.estado', '1')  // Filtrar personaes activos
            ->where('p.estado_delete', '1');  // Filtrar personaes no eliminados

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
        $personaes = $query->paginate($perPage, ['*'], 'page', $page);

        // Formatear los resultados antes de devolverlos
        $personaes->getCollection()->transform(function ($persona) {
            return [
                'idpersona'             => $persona->idpersona,
                'tipoPersona'           => $persona->tipoPersona,
                'tipo_documento'        => $persona->tipo_documento,
                'nombre_razonsocial'    => $persona->nombre_razonsocial,
                'apellidos_nombrecomercial' => $persona->apellidos_nombrecomercial,
                'abreviatura'           => $persona->abreviatura,
                'numero_documento'     => $persona->numero_documento,
                'celular'               => $persona->celular,
                'direccion'             => $persona->direccion,
                'distrito'              => $persona->distrito,
                'provincia'             => $persona->provincia,
                'departamento'          => $persona->departamento,
                'email'                 => $persona->email,
                'tipo_entidad_sunat'    => $persona->tipo_entidad_sunat,
                'estado'                => $persona->estado,
            ];
        });

        // Devolver la respuesta JSON con los resultados
        return response()->json([
            'data'         => $personaes->items(),
            'current_page' => $personaes->currentPage(),
            'per_page'     => $personaes->perPage(),
            'total'        => $personaes->total(),
            'last_page'    => $personaes->lastPage(),
            'from'         => $personaes->firstItem(),
            'to'           => $personaes->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    public function mostrar_editar_persona(Request $r, $idpersona)
    {
        try {
            // Solo los campos que el form necesita
            $mostrar_persona = persona::select([
                'idpersona',
                'idtipo_persona',
                'idbanco',
                'tipo_entidad_sunat',
                'tipo_documento',
                'numero_documento',
                'nombre_razonsocial',
                'celular',
                'direccion',
                'email',
                'distrito',
                'provincia',
                'departamento',
                'estado'
            ])
                ->whereKey($idpersona) // equivale a where primaryKey = $id
                ->firstOrFail();


            return ApiResponse::success($mostrar_persona, 'Persona obtenida correctamente');
        } catch (ModelNotFoundException $e) {
            // 404 semántico
            return ApiResponse::error(new \Exception('Persona no encontrada', 404), 404);
        } catch (\Throwable $e) {
            return ApiResponse::error($e, 500);
        }
    }

    // Método para obtener todos roles personas
    public function selec2Rolpersona()
    {
      try {
        $data  = Rolpersona::select2rolpersona();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idtipo_persona.'" >' . e($t->descripcion). '</option>';
        }

        return ApiResponse::success($options, 'Lista de rol persona obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }
    



}
