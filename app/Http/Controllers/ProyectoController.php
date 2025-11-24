<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProyectoController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {        
        return view('proyectos');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function crear_proyecto(Request $r)
    {
       try {
            $data = $r->validate([
                'codigo'            => ['required','string','max:50', 'unique:proyecto,codigo'],
                'descripcion'       => ['required','string','max:255'],
                'empresa'           => ['nullable','string','max:150'],
                'cliente'           => ['nullable','string','max:150'],
                'direccion'         => ['nullable','string','max:255'],
                'ubicacion'         => ['nullable','string','max:255'],
                'total_presupuesto' => ['nullable','numeric'],
                'fecha_inicio'      => ['nullable','date'],
                'fecha_fin'         => ['nullable','date','after_or_equal:fecha_inicio'],
            ]);

            $proyecto = Proyecto::create($data);

            return ApiResponse::success([
                'idproyecto' => $proyecto->idproyecto
            ], 'Proyecto creado correctamente');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Proyecto $proyecto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editar_proyecto(Request  $r, int $idproyecto)
    {
        try {
            $proyecto = Proyecto::findOrFail($idproyecto);

            $data = $r->validate([
                'codigo'            => ['required','string','max:50', Rule::unique('proyecto','codigo')->ignore($proyecto->idproyecto,'idproyecto')],
                'descripcion'       => ['required','string','max:255'],
                'empresa'           => ['nullable','string','max:150'],
                'cliente'           => ['nullable','string','max:150'],
                'direccion'         => ['nullable','string','max:255'],
                'ubicacion'         => ['nullable','string','max:255'],
                'total_presupuesto' => ['nullable','numeric'],
                'fecha_inicio'      => ['nullable','date'],
                'fecha_fin'         => ['nullable','date','after_or_equal:fecha_inicio'],
            ]);

            $proyecto->update($data);

            return ApiResponse::success([
                'idproyecto' => $proyecto->idproyecto
            ], 'Proyecto actualizado correctamente');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r, Proyecto $proyecto)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proyecto $proyecto)
    {
         // Si hay FK en presupuestos, pon ON DELETE RESTRICT o borra en cascada según tu política
        $proyecto->delete();
        return back()->with('ok','Proyecto eliminado');
    }

    public function ver_editar_proyecto(Request $request, int $idproyecto)
    {
        try {
            $proyecto = Proyecto::with('presupuestos')->findOrFail($idproyecto);

            // Respuesta usando tu Helper
            return ApiResponse::success([
                'idproyecto'        => $proyecto->idproyecto,
                'codigo'            => $proyecto->codigo,
                'descripcion'       => $proyecto->descripcion,
                'empresa'           => $proyecto->empresa,
                'cliente'           => $proyecto->cliente,
                'direccion'         => $proyecto->direccion,
                'ubicacion'         => $proyecto->ubicacion,
                'total_presupuesto' => $proyecto->total_presupuesto,
                'fecha_inicio'      => optional($proyecto->fecha_inicio)->format('Y-m-d'),
                'fecha_fin'         => optional($proyecto->fecha_fin)->format('Y-m-d'),
                'presupuestos'      => $proyecto->presupuestos,
            ], 'Proyecto encontrado');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

  // app/Http/Controllers/ProyectoController.php
    public function tabla_principal(Request $r)
    {
        $perPage = (int) $r->input('per_page', 20);           // tamaño de página
        $page    = (int) $r->input('page', 1);                // página actual
        $sort    = $r->input('sort', 'idproyecto');           // columna a ordenar
        $dir     = $r->input('dir', 'asc');                   // asc|desc
        $q       = trim($r->input('q', ''));                  // búsqueda global

        $validSorts = [
            'idproyecto','codigo','descripcion','cliente',
            'empresa','fecha_inicio','fecha_fin','total_presupuesto', 'direccion', 'ubicacion'
        ];
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idproyecto';
        }
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        $query = Proyecto::query()
            ->addSelect('*')
            ->addSelect(DB::raw("to_char(fecha_inicio,'YYYY-MM-DD') as fecha_inicio_ymd"))
            ->addSelect(DB::raw("to_char(fecha_fin,'YYYY-MM-DD') as fecha_fin_ymd"))
            ->addSelect(DB::raw("to_char(fecha_inicio,'DD/MM/YYYY') as fecha_inicio_dmy"))
            ->addSelect(DB::raw("to_char(fecha_fin,'DD/MM/YYYY') as fecha_fin_dmy"))
            ->when($q !== '', function ($w) use ($q) {

                $w->where(function($qq) use ($q){
                    $qq->whereRaw("LOWER(codigo) like ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(descripcion) like ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(cliente::text) like ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(empresa::text) like ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(total_presupuesto::text) like ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(direccion) like ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(ubicacion) like ?", ["%{$q}%"]);
                });
            })
            ->orderBy($sort, $dir);

        $p = $query->paginate($perPage, ['*'], 'page', $page);

        // formatea campos antes de devolver
        $p->getCollection()->transform(function($p){
            return [
                'idproyecto'        => $p->idproyecto,
                'codigo'            => $p->codigo,
                'descripcion'       => $p->descripcion,
                'cliente'           => $p->cliente,
                'empresa'           => $p->empresa,
                'fecha_inicio_ymd'  => $p->fecha_inicio_ymd,
                'fecha_fin_ymd'     => $p->fecha_fin_ymd,
                'fecha_inicio_dmy'  => $p->fecha_inicio_dmy,
                'fecha_fin_dmy'     => $p->fecha_fin_dmy,
                'total_presupuesto' => number_format((float)$p->total_presupuesto, 2, '.', ''),
                'direccion'         => $p->direccion,
                'ubicacion'         => $p->ubicacion,
            ];
        });

        return response()->json([
            'data'         => $p->items(),
            'current_page' => $p->currentPage(),
            'per_page'     => $p->perPage(),
            'total'        => $p->total(),
            'last_page'    => $p->lastPage(),
            'from'         => $p->firstItem(),
            'to'           => $p->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    // Endpoint AJAX que devuelve HTML renderizado
    public function detalleHtml($idproyecto)  // usa el mismo nombre que en la ruta
    {
        try {
            $proyecto = Proyecto::findOrFail($idproyecto);

            // Formatos de fecha (dd/mm/yyyy)
            $fecha_inicio = optional($proyecto->fecha_inicio)->format('d/m/Y');
            $fecha_fin    = optional($proyecto->fecha_fin)->format('d/m/Y');

            $html = view('componentes_erp.proyecto-detalle', [
                'proyecto'     => $proyecto,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin'    => $fecha_fin,
            ])->render();

            // Devuelve en "data" para que tu JS lo lea en e.data
            return ApiResponse::success($html);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

}