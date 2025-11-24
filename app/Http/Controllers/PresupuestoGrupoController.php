<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\PresupuestoGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PresupuestoGrupoController extends Controller
{
    public function arbolCompleto(Request $request)
    {
        try {
            // Asegura que exista "ESCRITORIO"
            $root = $this->ensureRootEscritorio();

            // Trae TODOS los grupos + sus presupuestos
            $grupos = PresupuestoGrupo::query()
                ->with(['presupuestos' => function ($q) {
                    $q->select(
                        'idpresupuesto',
                        'idpresupuesto_grupo',
                        'idproyecto',
                        'fecha',
                        'tipo',
                        'descripcion_resumen',
                        'icono',
                        'icono_color'
                    )->orderByDesc('fecha');
                    // ->activos()                        
                }])
                ->orderByRaw("CASE WHEN trim(descripcion) ILIKE 'ESCRITORIO' THEN 0 ELSE 1 END")
                ->orderBy('descripcion')
                ->get(['idpresupuesto_grupo', 'descripcion', 'icono', 'icono_color']);

            // Cada GRUPO es un nodo raíz (incluye ESCRITORIO)
            $tree = $grupos->map(function ($g) use ($root) {
                $grpIcon = $this->iconClass($g->icono, $g->icono_color);

                $childrenPresupuestos = $g->presupuestos->map(function ($p) {
                    $preIcon = $this->iconClass($p->icono, $p->icono_color);

                    return [
                        'id' => 'p_'.$p->idpresupuesto,                   // <--- mantiene prefijo
                        'text' => $p->descripcion_resumen ?: '(Sin resumen)',
                        'icon' => $preIcon,
                        'children' => false,
                        'a_attr' => [
                            'title' => sprintf(
                                'Proyecto: %s | Fecha: %s | Tipo: %s',
                                $p->idproyecto,
                                optional($p->fecha)->format('d/m/Y'),
                                $p->tipo
                            ),
                        ],
                        'li_attr' => [
                            'data-type' => 'presupuesto',
                            'data-idpresupuesto' => $p->idpresupuesto,
                            'data-idproyecto' => $p->idproyecto,
                        ],
                    ];
                });

                return [
                    'id' => 'g_'.$g->idpresupuesto_grupo,                 // <--- mantiene prefijo
                    'text' => $g->descripcion,
                    'icon' => $grpIcon,
                    'state' => ['opened' => ((int) $g->idpresupuesto_grupo === (int) $root->idpresupuesto_grupo)],
                    'children' => $childrenPresupuestos,
                    'li_attr' => [
                        'data-type' => 'grupo',
                        'data-idpresupuesto-grupo' => $g->idpresupuesto_grupo,
                    ],
                ];
            })->values();

            // devolvemos ARREGLO de raíces (multi-root)
            return ApiResponse::success($tree);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    private function ensureRootEscritorio(): PresupuestoGrupo
    {
        $root = PresupuestoGrupo::whereRaw('LOWER(descripcion) = ?', ['escritorio'])->first();

        if ($root) {
            return $root;
        }

        return DB::transaction(function () {
            $exists = PresupuestoGrupo::whereRaw('LOWER(descripcion) = ?', ['escritorio'])
                ->lockForUpdate()->first();
            if ($exists) {
                return $exists;
            }

            // crea con icono por defecto si no envías otros
            return PresupuestoGrupo::create([
                'descripcion' => 'ESCRITORIO',
                'icono' => 'ti ti-device-desktop', // si quieres uno fijo cámbialo aquí
                'icono_color' => 'text-info',
            ]);
        });
    }

    private function iconClass(?string $icono, ?string $iconoColor): string
    {
        $icono = trim((string) $icono);
        $iconoColor = trim((string) $iconoColor);

        $icono = $icono !== '' ? $icono : 'ri-folder-check-fill';
        $iconoColor = $iconoColor !== '' ? $iconoColor : 'text-info ';

        // Combinar ambas clases para jsTree (usa tus librerías de íconos)
        return $icono.' '.$iconoColor;
    }

    public function crear(Request $r)
    {
        try {
            $data = $r->validate([
                'descripcion' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('presupuesto_grupo', 'descripcion'),
                ],
                'icono' => ['nullable', 'string', 'max:100'],
                'icono_color' => ['nullable', 'string', 'max:100'],
            ]);
            $g = PresupuestoGrupo::create($data);

            return ApiResponse::success(['id' => $g->idpresupuesto_grupo], 'Grupo creado');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    // PresupuestoGrupoController.php
    public function actualizar_grupo(Request $r, $idpresupuesto_grupo)
    {
        try {
            $data = $r->validate([
                'descripcion' => ['required', 'string', 'max:255'],
                'icono' => ['nullable', 'string', 'max:100'],
                'icono_color' => ['nullable', 'string', 'max:100'],
            ]);

            $g = PresupuestoGrupo::findOrFail($idpresupuesto_grupo);

            // opcional: impedir renombrar ESCRITORIO
            if (mb_strtolower($g->descripcion) === 'escritorio' &&
                mb_strtolower($data['descripcion']) !== 'escritorio') {
                return ApiResponse::validation(['descripcion' => ['No puedes renombrar el grupo raíz.']], 'Validación');
            }

            $g->fill($data)->save();

            return ApiResponse::success(null, 'Grupo actualizado');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function mostrar_editar(PresupuestoGrupo $idpresupuesto_grupo)
    {
        try {
            return ApiResponse::success($idpresupuesto_grupo, 'Detalle del grupo');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }
}
