<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Presupuesto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PresupuestoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        return view('presupuestos');
    }

    public function crear_cabecera_presupuesto(Request $r)
    {
        $rules = [
            'idproyecto'            => ['nullable', 'integer', 'exists:proyecto,idproyecto'],
            'idpresupuesto_grupo'   => ['required', 'integer', 'exists:presupuesto_grupo,idpresupuesto_grupo'],
            'descripcion'           => ['required', 'string', 'max:200'],
            'descripcion_resumen'   => ['nullable', 'string', 'max:500'],
            'tipo'                  => ['nullable', 'string', 'max:50'],
            'icono'                 => ['nullable', 'string', 'max:100'],
            'icono_color'           => ['nullable', 'string', 'max:50'],
        ];

        $messages = [
            'idproyecto.required'           => 'Seleccione un proyecto.',
            'idproyecto.exists'             => 'El proyecto no existe.',
            'idpresupuesto_grupo.required'  => 'Seleccione un grupo.',
            'idpresupuesto_grupo.exists'    => 'El grupo no existe.',
            'descripcion.required'          => 'La descripción es obligatoria.',
        ];

        $v = Validator::make($r->all(), $rules, $messages);
        if ($v->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Hay errores de validación.',
                'errors'  => $v->errors(),
            ], 422);
        }

        try {
            $presupuesto = DB::transaction(function () use ($r) {
                $data = $r->only([
                    'idproyecto',
                    'idpresupuesto_grupo',
                    'descripcion',
                    'descripcion_resumen',
                    'tipo',
                    'icono',
                    'icono_color',
                ]);

                // Defaults útiles
                $data['fecha']         = now()->toDateString();           // hoy (Lima)
                $data['user_created']  = Auth::id();
                $data['user_updated']  = Auth::id();

                return Presupuesto::create($data);
            });

            return ApiResponse::success([
                'idpresupuesto' => $presupuesto->idpresupuesto,
                'presupuesto'   => $presupuesto,
            ], 'Presupuesto creado correctamente');
        } catch (\Throwable $e) {
            return ApiResponse::error($e, 500);
        }
    }

    public function actualizar_cabecera_presupuesto(Request $r, $idpresupuesto)
    {
        $rules = [
            'idproyecto'            => ['nullable', 'integer', 'exists:proyecto,idproyecto'],
            'idpresupuesto_grupo'   => ['required', 'integer', 'exists:presupuesto_grupo,idpresupuesto_grupo'],
            'descripcion'           => ['required', 'string', 'max:200'],
            'descripcion_resumen'   => ['nullable', 'string', 'max:500'],
            'tipo'                  => ['nullable', 'string', 'max:50'],
            'icono'                 => ['nullable', 'string', 'max:100'],
            'icono_color'           => ['nullable', 'string', 'max:50'],
        ];

        $messages = [
            'idproyecto.required'           => 'Seleccione un proyecto.',
            'idproyecto.exists'             => 'El proyecto no existe.',
            'idpresupuesto_grupo.required'  => 'Seleccione un grupo.',
            'idpresupuesto_grupo.exists'    => 'El grupo no existe.',
            'descripcion.required'          => 'La descripción es obligatoria.',
        ];

        $v = Validator::make($r->all(), $rules, $messages);
        if ($v->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Hay errores de validación.',
                'errors'  => $v->errors(),
            ], 422);
        }

        try {
            $presupuesto = DB::transaction(function () use ($r, $idpresupuesto) {
                $presupuesto = Presupuesto::findOrFail($idpresupuesto);

                $data = $r->only([
                    'idproyecto',
                    'idpresupuesto_grupo',
                    'descripcion',
                    'descripcion_resumen',
                    'tipo',
                    'icono',
                    'icono_color',
                ]);

                $presupuesto->fill($data);
                $presupuesto->user_updated = Auth::id();
                $presupuesto->save();

                return $presupuesto;
            });

            return ApiResponse::success([
                'idpresupuesto' => $presupuesto->idpresupuesto,
                'presupuesto'   => $presupuesto,
            ], 'Presupuesto creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e, 500);
        }
    }

    public function mostrar_editar(Request $r, $idpresupuesto)
    {
        try {
            // Solo los campos que el form necesita
            $presupuesto = Presupuesto::select([
                'idpresupuesto',
                'idproyecto',
                'idpresupuesto_grupo',
                'descripcion',
                'descripcion_resumen',
                'tipo',
                'icono',
                'icono_color',
            ])
                ->whereKey($idpresupuesto) // equivale a where primaryKey = $id
                ->firstOrFail();

            // (Opcional) Si quieres incluir etiquetas de relaciones:
            // $presupuesto->load([
            //     'proyecto:idproyecto,descripcion',
            //     'grupo:idpresupuesto_grupo,descripcion'
            // ]);

            return ApiResponse::success($presupuesto, 'Presupuesto obtenido correctamente');
        } catch (ModelNotFoundException $e) {
            // 404 semántico
            return ApiResponse::error(new \Exception('Presupuesto no encontrado', 404), 404);
        } catch (\Throwable $e) {
            return ApiResponse::error($e, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        $data = $r->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'fecha' => 'required|date',
            'tipo' => 'required|max:50',
        ]);
        $p = Presupuesto::create($data);
        return redirect()->route('presupuestos.edit', $p->id_presupuesto)->with('ok', 'Presupuesto creado');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $r, Presupuesto $presupuesto)
    {
        $data = $r->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'fecha' => 'required|date',
            'tipo' => 'required|max:50',
        ]);
        $presupuesto->update($data);
        return back()->with('ok', 'Presupuesto actualizado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Presupuesto $presupuesto)
    {
        $presupuesto->delete();
        return redirect()->route('presupuestos.index')->with('ok', 'Presupuesto eliminado');
    }
}
