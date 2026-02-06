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
                'tipo_plantilla' => 'string'
            ]);

            // variable que guarda el nombre del archivo
						$filename = ""; $archivo = "";

						// Si suben archivo nuevo
						if ($r->hasFile('doc1') && $r->file('doc1')->isValid()) {

								$file = $r->file('doc1');

								$date_now = now()->format('Ymd_His');
								$ext = $file->getClientOriginalExtension();

								$filename = $date_now . '__' . random_int(0, 20)
										. round(microtime(true))
										. random_int(21, 41)
										. '.' . $ext;

								// ✅ ruta dentro de public/
								$destino = public_path('uploads/plantillas');

								// crear carpeta si no existe
								if (!is_dir($destino)) {mkdir($destino, 0755, true); }

								// mover a public/uploads/docs_proveedor_estandar
								$file->move($destino, $filename);

                // Guarda nombre o ruta (recomendado guardar ruta relativa)
                $archivo = 'uploads/plantillas/' . $filename;
						}

            // Armar data base
            $dataCreate = [
                'descripcion'    => $r->descripcion_docs,
                'tipo_documento' => $r->tipo_plantilla,
                'estado_trash'   => '1',
                'estado_delete'  => '1',
                'user_created'   => auth()->id() ?? null,
            ];

            // 👉 Solo agregar archivo si existe
            if (!empty($archivo)) {
                $dataCreate['archivo'] = $archivo;
            }

            // Crear registro
            $documento = DocumentoTipoEstandar::create($dataCreate);

            return ApiResponse::success([
                'iddocumento_tipo_estandar' =>  $documento
            ], 'Documento creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function editar_documento_tipo_estandar(Request $r, int $iddocumento_tipo_estandar)
    {
      try {

          // Buscar documento
          $documento = DocumentoTipoEstandar::findOrFail($iddocumento_tipo_estandar);

          // Validación
          $r->validate([
              'descripcion_docs' => 'required|string|max:45',
              'tipo_plantilla'   => 'string',
          ]);

          $archivoNuevo = $r->input('doc_old_1');

          /*dd([
              'archivo_bd' => $documento->archivo,
              'ruta_fisica' => $ruta,
              'existe' => file_exists($ruta),
              'is_file' => is_file($ruta),
          ]);*/

          // 👉 Si suben nuevo archivo
          if ($r->hasFile('doc1') && $r->file('doc1')->isValid()) {

              /* =========================
              * 1️⃣ Eliminar archivo antiguo
              * ========================= */
              $ruta = public_path($documento->archivo);

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

              $destino = public_path('uploads/plantillas');

              if (!is_dir($destino)) {
                  mkdir($destino, 0755, true);
              }

              $file->move($destino, $filename);

              $archivoNuevo = 'uploads/plantillas/' . $filename;
          }

          /* =========================
          * 3️⃣ Armar data update
          * ========================= */
          $dataUpdate = [
              'descripcion'    => $r->descripcion_docs,
              'tipo_documento' => $r->tipo_plantilla,
              'user_updated'   => auth()->id() ?? null,
          ];

          // 👉 Solo actualizar archivo si se subió uno nuevo
          if ($archivoNuevo) {
              $dataUpdate['archivo'] = $archivoNuevo;
          }

          /* =========================
          * 4️⃣ Update
          * ========================= */
          $documento->update($dataUpdate);

          return ApiResponse::success(
              ['iddocumento_tipo_estandar' => $documento->id],
              'Documento actualizado correctamente'
          );

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
                'tipo_documento',
                'archivo',
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
                'tipo_documento'           => $doc->tipo_documento,
                'archivo'                  => $doc->archivo,
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
                    'descripcion',
                    'tipo_documento',
                    'archivo'

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
