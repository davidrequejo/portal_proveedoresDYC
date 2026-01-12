<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Banco;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BancoController extends Controller
{
    public function index(Request $req)
    {
        return view('banco');
    }

    public function crear_banco(Request $r)
    {
        try {

            // Validación
            $data = $r->validate([
                'codigo_bank_s10' => 'required|string|max:45',
                'descripcion'     => 'required|string|max:255',
                'abreviatura'     => 'nullable|string|max:45',
            ]);

            $banco = Banco::create([
                'codigo_bank_s10' => $r->codigo_bank_s10,
                'descripcion'     => $r->descripcion,
                'abreviatura'     => $r->abreviatura,
                'user_created'    => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idbanco' => $banco->idbanco
            ], 'Banco creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_banco(Request $r, int $idbanco)
    {
        try {

            // Validación
            $data = $r->validate([
                'codigo_bank_s10' => 'required|string|max:45',
                'descripcion'     => 'required|string|max:255',
                'abreviatura'     => 'nullable|string|max:45',
            ]);

            $banco = Banco::findOrFail($idbanco);

            $banco->update([
                'codigo_bank_s10' => $r->codigo_bank_s10,
                'descripcion'     => $r->descripcion,
                'abreviatura'     => $r->abreviatura,
                'user_updated'    => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idbanco' => $banco->idbanco
            ], 'Banco actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function eliminar_banco(Request $r, int $idbanco)
    {
        try {

            $banco = Banco::findOrFail($idbanco);

            // Eliminado lógico
            $banco->update([
                'estado_trash'  => '0',
                'user_delete'   => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'idbanco' => $banco->idbanco,
                'estado_trash' => 0
            ], 'Banco eliminado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function listar_banco(Request $r)
    {
        $perPage = (int) $r->input('per_page', 20);
        $page    = (int) $r->input('page', 1);
        $sort    = $r->input('sort', 'idbanco');
        $dir     = $r->input('dir', 'asc');
        $q       = trim($r->input('q', ''));

        $validSorts = ['codigo_bank_s10', 'descripcion', 'abreviatura', 'estado_trash'];

        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idbanco';
        }

        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';

        $query = DB::table('banco')
            ->select(
                'idbanco',
                'codigo_bank_s10',
                'descripcion',
                'abreviatura',
                'estado_trash'
            )
            ->where('estado_trash', '1')
            ->where('estado_delete', '1');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw('LOWER(codigo_bank_s10) LIKE ?', ["%{$q}%"])
                  ->orWhereRaw('LOWER(descripcion) LIKE ?', ["%{$q}%"])
                  ->orWhereRaw('LOWER(abreviatura) LIKE ?', ["%{$q}%"]);
            });
        }

        $query->orderBy($sort, $dir);

        $bancos = $query->paginate($perPage, ['*'], 'page', $page);

        $bancos->getCollection()->transform(function ($banco) {
            return [
                'idbanco'          => $banco->idbanco,
                'codigo_bank_s10'  => $banco->codigo_bank_s10,
                'descripcion'      => $banco->descripcion,
                'abreviatura'      => $banco->abreviatura,
                'estado_trash'     => $banco->estado_trash,
            ];
        });

        return response()->json([
            'data'         => $bancos->items(),
            'current_page' => $bancos->currentPage(),
            'per_page'     => $bancos->perPage(),
            'total'        => $bancos->total(),
            'last_page'    => $bancos->lastPage(),
            'from'         => $bancos->firstItem(),
            'to'           => $bancos->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    public function mostrar_banco(Request $r, int $idbanco)
    {
        try {

            $banco = Banco::select([
                    'idbanco',
                    'codigo_bank_s10',
                    'descripcion',
                    'abreviatura'
                ])
                ->whereKey($idbanco)
                ->firstOrFail();

            return ApiResponse::success($banco, 'Banco obtenido correctamente');

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(
                new \Exception('Banco no encontrado', 404),
                404
            );

        } catch (\Throwable $e) {

            return ApiResponse::error($e, 500);
        }
    }
}
