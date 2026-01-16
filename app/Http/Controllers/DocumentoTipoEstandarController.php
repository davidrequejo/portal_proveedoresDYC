<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\DocumentoTipoEstandar;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DocumentoTipoEstandarController extends Controller
{
    /*public function index(Request $req)
    {
        return view('documento_tipo_estandar');
    }*/

    public function crear_documento_tipo_estandar(Request $r)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion_docs' => 'required|string|max:45',
            ]);

            $documento = DocumentoTipoEstandar::create([
                'descripcion'   => $r->descripcion_docs,
                'estado_trash'  => '1',
                'estado_delete' => '1',
                'user_created'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'iddocumento_tipo_estandar' => 'Registro creado'
            ], 'Documento creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_documento_tipo_estandar(Request $r, int $iddocumento_tipo_estandar)
    {
        try {

            // Validación
            $data = $r->validate([
                'descripcion_docs' => 'required|string|max:45',
            ]);

            $documento = DocumentoTipoEstandar::findOrFail($iddocumento_tipo_estandar);

            $documento->update([
                'descripcion'  => $r->descripcion_docs,
                'user_updated' => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'iddocumento_tipo_estandar' => $documento->iddocumento_tipo_estandar
            ], 'Tipo de documento actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function eliminar_documento_tipo_estandar(Request $r, int $iddocumento_tipo_estandar)
    {
        try {

            $documento = DocumentoTipoEstandar::findOrFail($iddocumento_tipo_estandar);

            // Eliminado lógico
            $documento->update([
                'estado_trash' => '0',
                'user_delete'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'iddocumento_tipo_estandar' => $documento->iddocumento_tipo_estandar,
                'estado_trash' => 0
            ], 'Tipo de documento eliminado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function listar_documento_tipo_estandar(Request $r)
    {
        $perPage = (int) $r->input('per_page', 20);
        $page    = (int) $r->input('page', 1);
        $sort    = $r->input('sort', 'iddocumento_tipo_estandar');
        $dir     = $r->input('dir', 'asc');
        $q       = trim($r->input('q', ''));

        $validSorts = ['descripcion', 'estado_trash'];

        if (!in_array($sort, $validSorts, true)) {
            $sort = 'iddocumento_tipo_estandar';
        }

        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $query = DB::table('documento_tipo_estandar')
            ->select(
                'iddocumento_tipo_estandar',
                'descripcion',
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

        $documentos = $query->paginate($perPage, ['*'], 'page', $page);

        $documentos->getCollection()->transform(function ($doc) {
            return [
                'iddocumento_tipo_estandar' => $doc->iddocumento_tipo_estandar,
                'descripcion'              => $doc->descripcion,
                'estado_trash'             => $doc->estado_trash,
            ];
        });

        return response()->json([
            'data'         => $documentos->items(),
            'current_page' => $documentos->currentPage(),
            'per_page'     => $documentos->perPage(),
            'total'        => $documentos->total(),
            'last_page'    => $documentos->lastPage(),
            'from'         => $documentos->firstItem(),
            'to'           => $documentos->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    public function mostrar_documento_tipo_estandar(Request $r, int $iddocumento_tipo_estandar)
    {
        try {

            $documento = DocumentoTipoEstandar::select([
                    'iddocumento_tipo_estandar',
                    'descripcion'
                ])
                ->whereKey($iddocumento_tipo_estandar)
                ->firstOrFail();

            return ApiResponse::success($documento, 'Tipo de documento obtenido correctamente');

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(
                new \Exception('Tipo de documento no encontrado', 404),
                404
            );

        } catch (\Throwable $e) {

            return ApiResponse::error($e, 500);
        }
    }
}
