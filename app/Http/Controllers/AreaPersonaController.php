<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Area_persona;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AreaPersonaController extends Controller
{
    public function index(Request $req)
    {
        return view('area_persona');
    }

    public function crear_area_persona(Request $r)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion' => 'required|string|max:255',
            ]);

            $area = Area_persona::create([
                'descripcion'   => $r->descripcion,
                'estado_trash'  => '1',
                'estado_delete' => '1',
                'user_created'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idarea_persona' => $area->idarea_persona
            ], 'Área creada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_area_persona(Request $r, int $idarea_persona)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion' => 'required|string|max:255',
            ]);

            $area = Area_persona::findOrFail($idarea_persona);

            $area->update([
                'descripcion'  => $r->descripcion,
                'user_updated' => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idarea_persona' => $area->idarea_persona
            ], 'Área actualizada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function eliminar_area_persona(Request $r, int $idarea_persona)
    {
        try {

            $area = Area_persona::findOrFail($idarea_persona);

            // Eliminado lógico
            $area->update([
                'estado_trash' => '0',
                'user_delete'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idarea_persona' => $area->idarea_persona,
                'estado_trash'   => 0
            ], 'Área eliminada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function listar_area_persona(Request $r)
    {
        $perPage = (int) $r->input('per_page', 20);
        $page    = (int) $r->input('page', 1);
        $sort    = $r->input('sort', 'idarea_persona');
        $dir     = $r->input('dir', 'asc');
        $q       = trim($r->input('q', ''));

        $validSorts = ['descripcion', 'estado_trash'];

        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idarea_persona';
        }

        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $query = DB::table('area_persona')
            ->select(
                'idarea_persona',
                'descripcion',
                'estado_trash'
            )
            ->where('estado_trash', '1');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(descripcion) LIKE ?', ["%{$q}%"]);
            });
        }

        $query->orderBy($sort, $dir);

        $areas = $query->paginate($perPage, ['*'], 'page', $page);

        $areas->getCollection()->transform(function ($area) {
            return [
                'idarea_persona' => $area->idarea_persona,
                'descripcion'    => $area->descripcion,
                'estado_trash'   => $area->estado_trash,
            ];
        });

        return response()->json([
            'data'         => $areas->items(),
            'current_page' => $areas->currentPage(),
            'per_page'     => $areas->perPage(),
            'total'        => $areas->total(),
            'last_page'    => $areas->lastPage(),
            'from'         => $areas->firstItem(),
            'to'           => $areas->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    public function mostrar_area_persona(Request $r, int $idarea_persona)
    {
        try {

            $area = Area_persona::select([
                    'idarea_persona',
                    'descripcion'
                ])
                ->whereKey($idarea_persona)
                ->firstOrFail();

            return ApiResponse::success($area, 'Área obtenida correctamente');

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(
                new \Exception('Área no encontrada', 404),
                404
            );

        } catch (\Throwable $e) {

            return ApiResponse::error($e, 500);
        }
    }

    // Para Select2
    public function select2_area_persona()
    {
        $data = Area_persona::select2area_persona();

        return response()->json(
            $data->map(function ($row) {
                return [
                    'id'   => $row->idarea_persona,
                    'text' => $row->descripcion
                ];
            })
        );
    }
}
