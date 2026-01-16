<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\FechaHomologacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FechaHomologacionController extends Controller
{
    public function index(Request $req)
    {
        return view('fechahomologacion');
    }

    public function crear_fecha_homologacion(Request $r)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion'   => 'required|string|max:45',
                'fecha_inicio'  => 'required|date',
                'fecha_fin'     => 'required|date|after_or_equal:fecha_inicio',
            ]);

            $fecha = FechaHomologacion::create([
                'descripcion'   => $r->descripcion,
                'fecha_inicio'  => $r->fecha_inicio,
                'fecha_fin'     => $r->fecha_fin,
                'estado_trash'  => '1',
                'estado_delete' => '1',
                'user_created'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idfecha_homologacion' => $fecha->idfecha_homologacion
            ], 'Fecha de homologación creada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_fecha_homologacion(Request $r, int $idfecha_homologacion)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion'   => 'required|string|max:45',
                'fecha_inicio'  => 'required|date',
                'fecha_fin'     => 'required|date|after_or_equal:fecha_inicio',
            ]);

            $fecha = FechaHomologacion::findOrFail($idfecha_homologacion);

            $fecha->update([
                'descripcion'   => $r->descripcion,
                'fecha_inicio'  => $r->fecha_inicio,
                'fecha_fin'     => $r->fecha_fin,
                'user_updated'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idfecha_homologacion' => $fecha->idfecha_homologacion
            ], 'Fecha de homologación actualizada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function eliminar_fecha_homologacion(Request $r, int $idfecha_homologacion)
    {
        try {

            $fecha = FechaHomologacion::findOrFail($idfecha_homologacion);

            // Eliminado lógico
            $fecha->update([
                'estado_trash' => '0',
                'user_delete'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idfecha_homologacion' => $fecha->idfecha_homologacion,
                'estado_trash'        => 0
            ], 'Fecha de homologación eliminada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function listar_fecha_homologacion(Request $r)
    {
        $perPage = (int) $r->input('per_page', 20);
        $page    = (int) $r->input('page', 1);
        $sort    = $r->input('sort', 'idfecha_homologacion');
        $dir     = $r->input('dir', 'asc');
        $q       = trim($r->input('q', ''));

        $validSorts = [
            'descripcion',
            'fecha_inicio',
            'fecha_fin',
            'estado_trash'
        ];

        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idfecha_homologacion';
        }

        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $query = DB::table('fecha_homologacion')
            ->select(
                'idfecha_homologacion',
                'descripcion',
                'fecha_inicio',
                'fecha_fin',
                'estado_trash'
            )
            ->where('estado_trash', '1')
            ->where('estado_delete', '1');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(descripcion) LIKE ?', ["%{$q}%"]);
            });
        }

        $query->orderBy($sort, $dir);

        $fechas = $query->paginate($perPage, ['*'], 'page', $page);

        $fechas->getCollection()->transform(function ($fecha) {
            return [
                'idfecha_homologacion' => $fecha->idfecha_homologacion,
                'descripcion'         => $fecha->descripcion,
                'fecha_inicio'        => $fecha->fecha_inicio,
                'fecha_fin'           => $fecha->fecha_fin,
                'estado_trash'        => $fecha->estado_trash,
            ];
        });

        return response()->json([
            'data'         => $fechas->items(),
            'current_page' => $fechas->currentPage(),
            'per_page'     => $fechas->perPage(),
            'total'        => $fechas->total(),
            'last_page'    => $fechas->lastPage(),
            'from'         => $fechas->firstItem(),
            'to'           => $fechas->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    public function mostrar_fecha_homologacion(Request $r, int $idfecha_homologacion)
    {
        try {

            $fecha = FechaHomologacion::select([
                    'idfecha_homologacion',
                    'descripcion',
                    'fecha_inicio',
                    'fecha_fin'
                ])
                ->whereKey($idfecha_homologacion)
                ->firstOrFail();

            return ApiResponse::success(
                $fecha,
                'Fecha de homologación obtenida correctamente'
            );

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(
                new \Exception('Fecha de homologación no encontrada', 404),
                404
            );

        } catch (\Throwable $e) {

            return ApiResponse::error($e, 500);
        }
    }
}
