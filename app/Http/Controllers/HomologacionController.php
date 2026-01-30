<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Homologacion;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\DocsProveedorTipoEstandar;
use App\Mail\EstadoDocumentoLogisticaMail;
use App\Mail\NotificacionDocumentosHomologacionMail;
use Illuminate\Support\Facades\Mail;

class HomologacionController extends Controller
{

    public function crear_periodo_homologacion(Request $r)
    {
        try {
            // Validación
            $data = $r->validate([
                'idproveedor' => 'required',
                'idtipoestandarproveedor' => 'required',
                'descripcion_homologacion'  => 'required|string|max:45',
                'fecha_inicio_periodo'              => 'required|date',
                'fecha_fin_periodo'                 => 'required|date|after_or_equal:fecha_inicio',
            ]);

            $p_homologacion = Homologacion::create([
                'idpersona'   => $r->idproveedor,
                'descripcion'  => $r->descripcion_homologacion,
                'fecha_inicio'  => $r->fecha_inicio_periodo,
                'fecha_fin'     => $r->fecha_fin_periodo,
                'estado_trash'  => '1',
                'estado_delete' => '1',
                'user_created'  => auth()->id() ?? null,
            ]);

            $detalles = DB::table('detalletipoestandarproveedor')
            ->where('idtipoestandarproveedor', $r->idtipoestandarproveedor)
            ->where('estado_trash', '1')
            ->where('estado_delete', '1')
            ->get();

            $docsInsert = [];

            foreach ($detalles as $d) {
                $docsInsert[] = [
                    'idpersona'                  => $r->idproveedor,
                    'iddetalletipoestandarproveedor'  => $d->iddetalletipoestandarproveedor,
                    'idpersona_facha_homologacion' => $p_homologacion->idpersona_facha_homologacion,
                    'estado_revision'           => 'Pendiente', // o el que manejes
                    'estado_trash'               => '1',
                    'estado_delete'              => '1',
                    'user_created'               => auth()->id(),
                ];
            }

            DB::table('docsproveedortipoestandar')->insert($docsInsert);

            return ApiResponse::success([
                'Respuesta' => 'Creado correctamente'
            ], 'Fecha de homologación creada correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_periodo_homologacion(Request $r, $idpersona_facha_homologacion)
    {
        DB::beginTransaction();

        try {
            // 1) Validación
            $data = $r->validate([
                'idtipoestandarproveedor'   => 'required',
                'descripcion_homologacion'  => 'required|string|max:45',
                'fecha_inicio_periodo'      => 'required|date',
                'fecha_fin_periodo'         => 'required|date|after_or_equal:fecha_inicio_periodo',
            ]);

            // 2) Buscar el periodo existente
            $p_homologacion = Homologacion::where('idpersona_facha_homologacion', $idpersona_facha_homologacion)
                ->where('estado_trash', '1')
                ->where('estado_delete', '1')
                ->firstOrFail();

            // 3) Actualizar cabecera
            $p_homologacion->update([
                'descripcion'   => $r->descripcion_homologacion,
                'fecha_inicio'  => $r->fecha_inicio_periodo,
                'fecha_fin'     => $r->fecha_fin_periodo,
                'user_updated'  => auth()->id() ?? null, // si tienes este campo
            ]);

            // 4) Traer detalles (docs requeridos) del tipo estándar seleccionado
            $detallesIds = DB::table('detalletipoestandarproveedor')
                ->where('idtipoestandarproveedor', $r->idtipoestandarproveedor)
                ->where('estado_trash', '1')
                ->where('estado_delete', '1')
                ->pluck('iddetalletipoestandarproveedor')
                ->toArray();

            // 5) Traer docs actuales del periodo
            $docsActualesIds = DB::table('docsproveedortipoestandar')
                ->where('idpersona', $p_homologacion->idpersona)
                ->where('idpersona_facha_homologacion', $p_homologacion->idpersona_facha_homologacion)
                ->where('estado_trash', '1')
                ->pluck('iddetalletipoestandarproveedor')
                ->toArray();

            // 6) Diferencias
            $faltan = array_values(array_diff($detallesIds, $docsActualesIds)); // se insertan
            $sobran = array_values(array_diff($docsActualesIds, $detallesIds)); // se desactivan

            // 7) Desactivar los que sobran (según tu lógica de borrado)
            if (!empty($sobran)) {
                DB::table('docsproveedortipoestandar')
                    ->where('idpersona', $p_homologacion->idpersona)
                    ->where('idpersona_facha_homologacion', $p_homologacion->idpersona_facha_homologacion)
                    ->whereIn('iddetalletipoestandarproveedor', $sobran)
                    ->update([
                        'estado_delete' => '0',
                        'user_deleted'  => auth()->id() ?? null, // si existe
                    ]);
            }

            // 8) (Opcional recomendado) Reactivar si antes estaban borrados y vuelven a corresponder
            if (!empty($detallesIds)) {
                DB::table('docsproveedortipoestandar')
                    ->where('idpersona', $p_homologacion->idpersona)
                    ->where('idpersona_facha_homologacion', $p_homologacion->idpersona_facha_homologacion)
                    ->whereIn('iddetalletipoestandarproveedor', $detallesIds)
                    ->where('estado_delete', '0')
                    ->update([
                        'estado_delete' => '1',
                    ]);
            }

            // 9) Insertar los que faltan
            if (!empty($faltan)) {
                $docsInsert = [];
                foreach ($faltan as $iddet) {
                    $docsInsert[] = [
                        'idpersona'                        => $p_homologacion->idpersona,
                        'iddetalletipoestandarproveedor'   => $iddet,
                        'idpersona_facha_homologacion'     => $p_homologacion->idpersona_facha_homologacion,
                        'estado_revision'                  => 'PENDIENTE',
                        'estado_trash'                     => '1',
                        'estado_delete'                    => '1',
                        'user_created'                     => auth()->id() ?? null,
                    ];
                }

                DB::table('docsproveedortipoestandar')->insert($docsInsert);
            }

            DB::commit();

            return ApiResponse::success([
                'Respuesta' => 'Editado correctamente'
            ], 'Periodo de homologación actualizado correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error($e);
        }
    }

    public function eliminar_fecha_homologacion(Request $r, int $idfecha_homologacion)
    {
        try {

            $fecha = Homologacion::findOrFail($idfecha_homologacion);

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

    public function listar_periodo_homologacion(Request $r)
    {

      try {
          $idpersona = $r->input('idpersona');

     $data = DB::table('persona_facha_homologacion as pfh')
        ->join(
            'docsproveedortipoestandar as docs',
            'docs.idpersona_facha_homologacion',
            '=',
            'pfh.idpersona_facha_homologacion'
        )
        ->join(
            'detalletipoestandarproveedor as dtp',
            'dtp.iddetalletipoestandarproveedor',
            '=',
            'docs.iddetalletipoestandarproveedor'
        )
        ->join(
            'tipoestandarproveedor as tep',
            'tep.idtipoestandarproveedor',
            '=',
            'dtp.idtipoestandarproveedor'
        )
        ->select(
            'pfh.idpersona_facha_homologacion',
            'pfh.descripcion',
            'pfh.fecha_inicio',
            'pfh.fecha_fin',
            'pfh.estado_trash',

            // 👉 dato adicional
            'tep.descripcion as tipo_estandar'
        )
        ->where('pfh.estado_trash', '1')
        ->where('pfh.estado_delete', '1')
        ->where('pfh.idpersona', $idpersona)
        ->groupBy(
            'pfh.idpersona_facha_homologacion',
            'pfh.descripcion',
            'pfh.fecha_inicio',
            'pfh.fecha_fin',
            'pfh.estado_trash',
            'tep.descripcion'
        )
        ->get();

          return ApiResponse::success($data, 'Cateogria de proveedores obtenidos');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }


    }

    public function mostrar_periodo_homologacion(Request $r, int $idpersona_facha_homologacion)
    {
        try {

            $fecha = Homologacion::select([
                    'idpersona_facha_homologacion',
                    'idpersona',        // <- en tu create guardas idpersona
                    'descripcion',
                    'fecha_inicio',
                    'fecha_fin'
                ])
                ->where('idpersona_facha_homologacion', $idpersona_facha_homologacion)
                ->where('estado_trash', '1')
                ->where('estado_delete', '1')
                ->firstOrFail();

            $idTipo = DB::table('docsproveedortipoestandar as dp')
                ->join('detalletipoestandarproveedor as dt', 'dt.iddetalletipoestandarproveedor', '=', 'dp.iddetalletipoestandarproveedor')
                ->where('dp.idpersona_facha_homologacion', $idpersona_facha_homologacion)
                ->distinct()
                ->value('dt.idtipoestandarproveedor');

            return ApiResponse::success([
                'data_homolog' => $fecha,
                'idtipoestandarproveedor' => $idTipo
            ], 'Fecha de homologación obtenida correctamente');

        } catch (ModelNotFoundException $e) {

            return ApiResponse::error(
                new \Exception('Fecha de homologación no encontrada', 404),
                404
            );

        } catch (\Throwable $e) {

            return ApiResponse::error($e, 500);
        }
    }

    // listar tipos de estandar y documentos asociados
    public function listar_docs_xperiodo_xproveedor(Request $r)
    {
        try {

            $idPeriodo = $r->input('idperiodo_homologacion');
            $idpersona = $r->input('idpersona'); // puede venir o no

            $data = DB::table('docsproveedortipoestandar as docs')
                ->join( 'persona_facha_homologacion as pfh', 'docs.idpersona_facha_homologacion', '=', 'pfh.idpersona_facha_homologacion' )
                ->join( 'detalletipoestandarproveedor as dtp', 'docs.iddetalletipoestandarproveedor', '=', 'dtp.iddetalletipoestandarproveedor' )
                ->join( 'documento_tipo_estandar as destts', 'destts.iddocumento_tipo_estandar', '=', 'dtp.iddocumento_tipo_estandar' )
                ->select(
                    'destts.descripcion',
                    'docs.iddocsproveedortipoestandar',
                    'docs.idpersona',
                    'docs.nombreDocumento',
                    'docs.archivo',
                    'docs.estado_revision',
                    'docs.estado_trash',
                    'docs.estado_delete'
                )
                ->where('pfh.idpersona_facha_homologacion', $idPeriodo)
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1')

                // 👇 FILTRO CONDICIONAL
                ->when($idpersona, function ($q) use ($idpersona) {
                    $q->where('docs.idpersona', $idpersona);
                })

                ->get();

            return ApiResponse::success($data, 'Documentos obtenidos correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    // enviar correo notificación
    public function enviar_correo_notificacion(Request $r)
    {
        try {

            $idpersona = $r->input('idpersona');
            $idperiodo_homologacion = $r->input('idperiodo_homologacion');

            // 1. Obtener proveedor
            $proveedor = Proveedor::where('idpersona', $idpersona)->first();

            // 2. Usuario de logística (quien hace la acción)
            $usuarioLogistica = auth()->user();

            // 3. Obtener documentos del periodo
            $documentos = DB::table('docsproveedortipoestandar as docs')
                ->join( 'persona_facha_homologacion as pfh', 'docs.idpersona_facha_homologacion', '=', 'pfh.idpersona_facha_homologacion' )
                ->join( 'detalletipoestandarproveedor as dtp', 'docs.iddetalletipoestandarproveedor', '=', 'dtp.iddetalletipoestandarproveedor' )
                ->join( 'documento_tipo_estandar as destts', 'destts.iddocumento_tipo_estandar', '=', 'dtp.iddocumento_tipo_estandar' )
                ->select(
                    'destts.descripcion',
                    'docs.estado_revision',
                    'docs.observacion'
                )
                ->where('pfh.idpersona_facha_homologacion', $idperiodo_homologacion)
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1')
                ->where('docs.idpersona', $idpersona)
                ->get();

            // 4. Enviar correo al proveedor
            Mail::to($proveedor->email)->queue(
                new NotificacionDocumentosHomologacionMail(
                    $proveedor,
                    $documentos,
                    $usuarioLogistica
                )
            );

            return ApiResponse::success([], 'Correo de notificación enviado al proveedor');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }


    
    // actualizar estado y observacion del documento estandar
    public function actualizar_estado_doc_estandar(Request $r, $iddocsproveedortipoestandar)
    {
        try {

            // 1) Buscar documento y validar pertenencia
            $doc = DocsProveedorTipoEstandar::where('iddocsproveedortipoestandar', $iddocsproveedortipoestandar)
                ->firstOrFail();

            // 2) Actualizar SOLO los campos permitidos
            $doc->estado_revision = $r->input('estado_documentos_update');
            $doc->observacion     = $r->input('observacion_est_up');

            // 3) Guardar cambios
            $doc->save();


            // 2. Obtener proveedor
           // $proveedor = Proveedor::where('idpersona', $doc->idpersona)->first();

            // 3. Usuario de logística (quien hace la acción)
           /* $usuarioLogistica = auth()->user();

            // 4. Enviar correo al proveedor
            Mail::to($proveedor->email)->send(
                new EstadoDocumentoLogisticaMail(
                    $proveedor,
                    $doc->nombreDocumento,
                    $doc->estado_revision,
                    $doc->observacion,
                    $usuarioLogistica
                )
            );*/

            return ApiResponse::success([], 'Estado actualizado y correo enviado');

            //return ApiResponse::success('Actualizado', 'Estado del documento actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }











}
