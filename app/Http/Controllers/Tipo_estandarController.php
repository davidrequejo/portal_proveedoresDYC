<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Tipo_estandar;
use App\Models\Tipo_estandarDetalle;
use Illuminate\Support\Facades\DB; 



class Tipo_estandarController extends Controller
{
   public function index(Request $req)
    {        
        return view('tipo_estandar');
    }

    public function crear_tipoestandar(Request $r)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion'   => 'required|string',
                'nroDocumentos' => 'required|integer|min:1',
                'detalle'       => 'nullable|array',
                'detalle.*'     => 'nullable|string',
            ]);

            // Crear el tipo de estándar
            $createtipo = Tipo_estandar::create([
                'descripcion'   => $r->descripcion,
                'nroDocumentos' => $r->nroDocumentos
            ]);

            // Registrar detalles (solo si hay valores)
            if ($r->detalle) {
                foreach ($r->detalle as $item) {

                    // Si está vacío, NO lo guardamos
                    if (trim($item) == '') {
                        continue;
                    } 

                    Tipo_estandarDetalle::create([
                        'idtipoestadandarproveedor' => $createtipo->idtipoestandarproveedor,
                        'detalle' => $item,
                    ]);
                }
            }

            return ApiResponse::success([
                'id' => $createtipo->idtipoestandarproveedor
            ], 'Proveedor creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_tipoestandar(Request $r, $id)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion'   => 'required|string',
                'nroDocumentos' => 'required|integer|min:1',
                'detalle'       => 'nullable|array',
                'detalle.*'     => 'nullable|string',
            ]);

            // Buscar maestro
            $tipo = Tipo_estandar::findOrFail($id);

            // Actualizar maestro
            $tipo->update([
                'descripcion'   => $r->descripcion,
                'nroDocumentos' => $r->nroDocumentos
            ]);

            // 🔥 ELIMINAR TODOS LOS DETALLES ANTERIORES
            Tipo_estandarDetalle::where('idtipoestandarproveedor', $id)->delete();


            // 🔥 VOLVER A REGISTRAR NUEVOS DETALLES
            if ($r->detalle) {
                foreach ($r->detalle as $item) {

                    if (trim($item) == '') {
                        continue; // no guardar vacíos
                    }

                    Tipo_estandarDetalle::create([
                        'idtipoestandarproveedor' => $id,
                        'detalle' => $item,
                    ]);
                }
            }

            return ApiResponse::success([
                'id' => $tipo->idtipoestandarproveedor
            ], 'Tipo estándar actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function eliminar_tipoestandar(Request $r, int $idtipoestandarproveedor)
    {
        try {
            $tipoestandar = Tipo_estandar::findOrFail($idtipoestandarproveedor);

            // Actualizamos únicamente el estado a 0
            $tipoestandar->update([ 'estado_trash' => 0 ]);

            return ApiResponse::success([
                'idtipoestandarproveedor' => $tipoestandar->idtipoestandarproveedor,
                'estado_trash' => 0
            ], 'Eliminado correctamente');
            
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function Listar_tipoestandar(Request $r)
    {
        // Parámetros de entrada del request
        $perPage = (int) $r->input('per_page', 20);           // Número de elementos por página (por defecto 20)
        $page    = (int) $r->input('page', 1);                // Página actual (por defecto 1)
        $sort    = $r->input('sort', 'idtipoestandarproveedor');            // Columna a ordenar (por defecto 'idpersona')
        $dir     = $r->input('dir', 'asc');                   // Dirección de orden ('asc' o 'desc')
        $q       = trim($r->input('q', ''));                  // Término de búsqueda global

        // Columnas válidas para ordenar
        $validSorts = [ 'descripcion', 'nroDocumentos','estado_trash' ];

        // Si la columna para ordenar no es válida, usamos 'idpersona'
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idtipoestandarproveedor';
        }
        // Asegurarse de que la dirección de orden sea 'asc' o 'desc'
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        // Crear la consulta base
        $query = DB::table('tipoestandarproveedor')->select( 'idtipoestandarproveedor', 'descripcion', 'nroDocumentos','estado_trash' ) ->where('estado_trash', '1') ->where('estado_delete', '1'); 

        // Si hay un término de búsqueda, lo aplicamos en las columnas
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw("LOWER(descripcion) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(nroDocumentos) LIKE ?", ["%{$q}%"]);
            });
        }

        // Ordenar los resultados
        $query->orderBy($sort, $dir);

        // Paginación
        $tipoestandar = $query->paginate($perPage, ['*'], 'page', $page);

        // Formatear los resultados antes de devolverlos
        $tipoestandar->getCollection()->transform(function ($tipoestandar) {
            return [
                'idtipoestandarproveedor' => $tipoestandar->idtipoestandarproveedor,
                'descripcion'             => $tipoestandar->descripcion,
                'nroDocumentos'           => $tipoestandar->nroDocumentos,
                'estado_trash'            => $tipoestandar->estado_trash
            ];
        });

        // Devolver la respuesta JSON con los resultados
        return response()->json([
            'data'         => $tipoestandar->items(),
            'current_page' => $tipoestandar->currentPage(),
            'per_page'     => $tipoestandar->perPage(),
            'total'        => $tipoestandar->total(),
            'last_page'    => $tipoestandar->lastPage(),
            'from'         => $tipoestandar->firstItem(),
            'to'           => $tipoestandar->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    public function mostrar_tipoestandar(Request $r, $idtipoestandarproveedor)
    {
       try {

            $tipo_estandar = Tipo_estandar::select([
                    'idtipoestandarproveedor',
                    'descripcion',
                    'nroDocumentos'
                ])
                ->whereKey($idtipoestandarproveedor)
                ->with(['detalles:iddetalletipoestandarproveedor,detalle,idtipoestandarproveedor'])
                ->firstOrFail();

            return ApiResponse::success($tipo_estandar, 'Tipo estandar obtenido correctamente');

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(new \Exception('Tipo Estandar no encontrado', 404), 404);

        } catch (\Throwable $e) {

            return ApiResponse::error($e, 500);
        }
    }












}
