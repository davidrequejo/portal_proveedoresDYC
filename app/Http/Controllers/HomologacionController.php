<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Homologacion;
use App\Models\Proveedor;
use App\Models\DocsProveedorTipoEstandar;
use App\Models\RegistrarNotifiHomolog_fph;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Mail\EstadoDocumentoLogisticaMail;
use App\Mail\NotificacionDocumentosHomologacionMail;
use App\Mail\NotificacionNuevaHomologacionMail;
use Illuminate\Support\Facades\Mail;

class HomologacionController extends Controller
{

    public function crear_periodo_homologacion(Request $r)
    {
        try {
            // Validación
            $data = $r->validate([
                'idproveedor' => 'required',
                'idpersonacomprador' => 'required',
                'idtipoestandarproveedor' => 'required',
                'descripcion_homologacion'  => 'string|max:45',
                'fecha_inicio_proceso' => 'required|date',
            ]);

            $p_homologacion = Homologacion::create([
                'idpersona'   => $r->idproveedor,
                'idpersonacomprador' => $r->idpersonacomprador,
                'descripcion'  => $r->descripcion_homologacion,
                'fecha_inicio_proceso'  => $r->fecha_inicio_proceso,
                'estado_trash'  => '1',
                'estado_delete' => '1',
                'user_init_process'  => auth()->id() ?? null,
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

						//envio de Notifición correo

						// 1. Obtener proveedor
            $proveedor = Proveedor::where('idpersona', $p_homologacion->idpersona)->first();

						// 2. Usuario de logística (quien hace la acción)
            $nombreSoporte = auth()->user()->persona?->nombre_razonsocial;
            $correoSoporte = auth()->user()->persona?->email;

						// 4. Enviar correo al proveedor
            Mail::to($proveedor->email)->queue(
                new NotificacionNuevaHomologacionMail(
                    $proveedor,
                    $nombreSoporte,
                    $correoSoporte
                )
            );

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
                'idpersonacomprador'        => 'required',
                'descripcion_homologacion'  => 'string|max:45',
                'fecha_inicio_proceso'      => 'required|date',
               //'fecha_fin_periodo'         => 'required|date|after_or_equal:fecha_inicio_periodo',
            ]);

            // 2) Buscar el periodo existente
            $p_homologacion = Homologacion::where('idpersona_facha_homologacion', $idpersona_facha_homologacion)
                ->where('estado_trash', '1')
                ->where('estado_delete', '1')
                ->firstOrFail();

            // 3) Actualizar cabecera
            $p_homologacion->update([
                'idpersonacomprador' => $r->idpersonacomprador,
                'idtipoestandarproveedor' => $r->idtipoestandarproveedor,
                'descripcion'   => $r->descripcion_homologacion,
                'fecha_inicio_proceso'  => $r->fecha_inicio_proceso,
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

    public function establecerfechas_periodo_homologacion(Request $r, $idpersona_facha_homologacion) {
        try {
            // 1) Validación
            $data = $r->validate([
                'idtipoestandarproveedor'   => 'required',
                'fecha_inicio_periodo_h'      => 'required|date',
                'fecha_fin_periodo_h'   => 'required|date|after_or_equal:fecha_inicio_periodo_h',
            ]);

            // 2) Buscar el periodo existente
            $p_homologacion = Homologacion::where('idpersona_facha_homologacion', $idpersona_facha_homologacion)
                ->where('estado_trash', '1')
                ->where('estado_delete', '1')
                ->firstOrFail();

            // 3) Actualizar 
            $p_homologacion->update([
                'fecha_fin_periodo_h'  => $r->fecha_fin_periodo_h,
                'fecha_inicio_periodo_h'     => $r->fecha_inicio_periodo_h,
                'estado_homologacion' => 'Vigente',
                'user_fin_process'  => auth()->id() ?? null,
            ]);

            return ApiResponse::success([
                'Respuesta' => 'Fechas Establecidas correctamente'
            ], 'Periodo de homologación actualizado correctamente');

        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error($e);
        }

    }

    public function eliminar_eliminar_periodo_h(Request $r, int $idfecha_homologacion)
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
          ->join('docsproveedortipoestandar as docs', 'docs.idpersona_facha_homologacion', '=', 'pfh.idpersona_facha_homologacion')
          ->join('detalletipoestandarproveedor as dtp', 'dtp.iddetalletipoestandarproveedor', '=', 'docs.iddetalletipoestandarproveedor')
          ->join('tipoestandarproveedor as tep', 'tep.idtipoestandarproveedor', '=', 'dtp.idtipoestandarproveedor')
          ->select( 'pfh.idpersona_facha_homologacion', 'pfh.descripcion', 'pfh.fecha_inicio_proceso', 'pfh.fecha_fin', 'pfh.fecha_inicio_periodo_h', 
              'pfh.fecha_fin_periodo_h', 'pfh.estado_homologacion', 'pfh.estado_trash', 'tep.descripcion as tipo_estandar','pfh.notificado_15dias',
              DB::raw(" CASE WHEN COUNT(docs.iddocsproveedortipoestandar) = SUM(CASE WHEN docs.estado_revision = 'Aprobado' THEN 1 ELSE 0 END) THEN 1 ELSE 0 END as todo_aprobado ")
          )
          ->where('pfh.estado_trash', '1')
          ->where('pfh.estado_delete', '1')
          ->where('pfh.idpersona', $idpersona)
          ->groupBy(
              'pfh.idpersona_facha_homologacion',
              'pfh.descripcion',
              'pfh.fecha_inicio_proceso',
              'pfh.fecha_fin',
              'pfh.fecha_inicio_periodo_h',
              'pfh.fecha_fin_periodo_h',
              'pfh.estado_homologacion',
              'pfh.estado_trash',
              'notificado_15dias',
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
                    'idpersonacomprador', 
                    'descripcion',
                    'fecha_inicio_proceso',
                    'fecha_fin',
                    'fecha_inicio_periodo_h',
                    'fecha_fin_periodo_h',
                    'estado_homologacion'
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

            $data_estandar = DB::table('docsproveedortipoestandar as docs')
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
                ->whereIn('destts.tipo_documento', ['Estandar', 'Modelo'])
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1')
                ->when($idpersona, function ($q) use ($idpersona) {
                    $q->where('docs.idpersona', $idpersona);
                })
                ->get();

            $data_interno = DB::table('docsproveedortipoestandar as docs')
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
                ->where('destts.tipo_documento', 'Interno')
                ->where('pfh.estado_trash', '1')
                ->where('pfh.estado_delete', '1')
                ->when($idpersona, function ($q) use ($idpersona) {
                    $q->where('docs.idpersona', $idpersona);
                })
                ->get();

            return ApiResponse::success(
              [
                'data_est' => $data_estandar,
                'data_int'   => $data_interno
              ], 'Documentos obtenidos correctamente');

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
            //$usuarioLogistica = auth()->user();
            $nombreSoporte = auth()->user()->persona?->nombre_razonsocial;
            $correoSoporte = auth()->user()->persona?->email;

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
								->whereIn('destts.tipo_documento', ['Estandar', 'Modelo'])
                ->where('pfh.estado_delete', '1')
                ->where('docs.idpersona', $idpersona)
                ->get();

            // 4. Enviar correo al proveedor
            Mail::to($proveedor->email)->queue(
                new NotificacionDocumentosHomologacionMail(
                    $proveedor,
                    $documentos,
                    $nombreSoporte,
                    $correoSoporte,
                    $idperiodo_homologacion
                )
            );

            return ApiResponse::success([], 'Correo de notificación enviado al proveedor');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    //mostrar ultimo envio de correo
    public function show_ultimo_envio_notificacion($id)
    {
        try {
        // 🔥 OBTENER LA ÚLTIMA NOTIFICACIÓN ENVIADA
        $ultimaNotificacion = RegistrarNotifiHomolog_fph::where('idpersona_facha_homologacion', $id)
            //->where('estado', 'enviado')
            ->latest('fecha_envio')
            ->first();
        
            return ApiResponse::success([$ultimaNotificacion], 'fecha ultimo envio correo enviado');

            //return ApiResponse::success('Actualizado', 'Estado del documento actualizado correctamente');

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

            return ApiResponse::success([], 'Estado actualizado y correo enviado');

            //return ApiResponse::success('Actualizado', 'Estado del documento actualizado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    // Cargar Documento interno
    public function cargar_documento_interno_estandar(Request $r, $iddocsproveedortipoestandar)
    {
        try {

          // 1) Buscar documento y validar pertenencia
          $doc = DocsProveedorTipoEstandar::where('iddocsproveedortipoestandar', $iddocsproveedortipoestandar)->firstOrFail();

          $archivoNuevo = $r->input('doc_old_1');

          // 👉 Si suben nuevo archivo
          if ($r->hasFile('doc1') && $r->file('doc1')->isValid()) {

              /* =========================
              * 1️⃣ Eliminar archivo antiguo
              * ========================= */
              $ruta = public_path($doc->archivo);

              if (is_file($ruta)) {
                  // 🔥 CLAVE EN WINDOWS
                  @chmod($ruta, 0777);

                  if (!@unlink($ruta)) {
                      dd('NO SE PUDO BORRAR', $ruta);
                  }
              }

              /* =========================
              * 2️⃣ Guardar nuevo archivo
              * ========================= */
              $file = $r->file('doc1');

              $date_now = now()->format('Ymd_His');
              $ext = $file->getClientOriginalExtension();

              $filename = $date_now . '__'
                  . random_int(0, 20)
                  . round(microtime(true))
                  . random_int(21, 41)
                  . '.' . $ext;

              $destino = public_path('uploads/docs_proveedor_estandar');

              if (!is_dir($destino)) {
                  mkdir($destino, 0755, true);
              }

              $file->move($destino, $filename);

              $archivoNuevo = 'uploads/docs_proveedor_estandar/' . $filename;
          }

          /* =========================
          * 3️⃣ Armar data update
          * ========================= */
          $dataUpdate = [
              'estado_revision'    =>'Actualizado',
              'observacion' => $r->input('observacion_est_up'),
              'user_updated'   => auth()->id() ?? null,
          ];

          // 👉 Solo actualizar archivo si se subió uno nuevo
          if ($archivoNuevo) {
              $dataUpdate['archivo'] = $archivoNuevo;
          }

          /* =========================
          * 4️⃣ Update
          * ========================= */
          $doc->update($dataUpdate);

          return ApiResponse::success(
              'Documento interno Cargado Correctamente',
              'Documento actualizado correctamente'
          );










            return ApiResponse::success([], 'Estado actualizado');


        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }











}
