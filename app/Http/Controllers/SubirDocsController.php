<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocsProveedorTipoEstandar;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiResponse;

class SubirDocsController extends Controller
{
        public function index()
    {
       return view('subir_docs');
    }


    //agregar documento estandar
		public function guardar_doc_estandar_proveedor(Request $r)
		{
				try {
						$doc = new DocsProveedorTipoEstandar();
						$doc->idpersona = $r->user()->idpersona;
						$doc->iddetalletipoestandarproveedor = $r->input('listar_docs_sin_subir');
						$doc->nombreDocumento = $r->input('nombre_seleccion_tipo');

						// Por defecto: archivo anterior (si no suben uno nuevo)
						$filename = $r->input('doc_old_1');

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
								$destino = public_path('uploads/docs_proveedor_estandar');

								// crear carpeta si no existe
								if (!is_dir($destino)) {mkdir($destino, 0755, true); }

								// mover a public/uploads/docs_proveedor_estandar
								$file->move($destino, $filename);
						}

						// Guarda nombre o ruta (recomendado guardar ruta relativa)
						$doc->archivo = 'uploads/docs_proveedor_estandar/' . $filename;

						/*$doc->estado_revision = 'Pendiente';
						$doc->estado_trash = 0;
						$doc->estado_delete = 0;*/
						$doc->save();

						return ApiResponse::success('Guardado', 'Documento estandar guardado correctamente');
				} catch (\Throwable $e) {
						return ApiResponse::error($e);
				}
		}

		// editar documento estandar
		public function editar_doc_estandar_proveedor(Request $r, $iddocsproveedortipoestandar)
		{
				try {
						$userId = $r->user()->idpersona;

						// 1) Buscar doc y validar pertenencia
						$doc = DocsProveedorTipoEstandar::where('iddocsproveedortipoestandar', $iddocsproveedortipoestandar)
								->where('idpersona', $userId)
								->firstOrFail();

						// 2) Actualizar campos (ajusta según tu form)
						$doc->idpersona = $userId;
						$doc->iddetalletipoestandarproveedor = $r->input('listar_docs_sin_subir', $doc->iddetalletipoestandarproveedor);
						$doc->nombreDocumento = $r->input('nombre_seleccion_tipo', $doc->nombreDocumento);

						// 3) Si suben archivo nuevo, borrar anterior y guardar nuevo
						if ($r->hasFile('doc1') && $r->file('doc1')->isValid()) {

								// borrar archivo anterior si existe
								if (!empty($doc->archivo)) {
										$oldPath = public_path($doc->archivo); // doc->archivo es ruta relativa tipo uploads/...
										if (is_file($oldPath)) {
												@unlink($oldPath);
										}
								}

								$file = $r->file('doc1');

								$date_now = now()->format('Ymd_His');
								$ext = $file->getClientOriginalExtension();

								$filename = $date_now . '__' . random_int(0, 20)
										. round(microtime(true))
										. random_int(21, 41)
										. '.' . $ext;

								$destino = public_path('uploads/docs_proveedor_estandar');
								if (!is_dir($destino)) {
										mkdir($destino, 0755, true);
								}

								$file->move($destino, $filename);

								// guardar ruta relativa nueva
								$doc->archivo = 'uploads/docs_proveedor_estandar/' . $filename;
						}

						// 4) Guardar cambios
						$doc->save();

						return ApiResponse::success('Actualizado', 'Documento estándar actualizado correctamente');

				} catch (\Throwable $e) {
						return ApiResponse::error($e);
				}
		}

		// eliminar documento estandar
		public function eliminar_doc_estandar_proveedor(Request $r, $iddocsproveedortipoestandar)
		{
			try {
				$userId = $r->user()->idpersona;

				// 1) Buscar documento y validar que pertenezca al usuario
				$doc = DocsProveedorTipoEstandar::where('iddocsproveedortipoestandar', $iddocsproveedortipoestandar)
					->where('idpersona', $userId)
					->firstOrFail();

				// 2) Eliminar archivo físico si existe
				if (!empty($doc->archivo)) {
					$path = public_path($doc->archivo); // ruta relativa almacenada en BD
					if (is_file($path)) {
						@unlink($path);
					}
				}

				// 3) Eliminar registro de BD
				$doc->delete();

				return ApiResponse::success('Eliminado', 'Documento estándar eliminado correctamente');

			} catch (\Throwable $e) {
				return ApiResponse::error($e);
			}
		}
		
		// Mostrar para editar documento estandar
		public function ver_doc_estandar(Request $r, $id)
		{
				try {
						//$id = $r->input('iddocsproveedortipoestandar'); // o $r->get(...)

						$doc = DocsProveedorTipoEstandar::where('iddocsproveedortipoestandar', $id)->first();

						if (!$doc) {
								return ApiResponse::error(null, 'No se encontró el registro');
						}

						return ApiResponse::success($doc, 'Registro obtenido');
				} catch (\Throwable $e) {
						return ApiResponse::error($e);
				}
		}

	  // listar para tabla tipos de estandar y documentos asociados
    public function listar_docs_tipos_est_xuser(Request $r)
    {
        $userId = $r->user()->idpersona; // usuario logueado

        try {
            $data = DB::table('tipoestandarproveedor as est')
                ->join(
                    'detalletipoestandarproveedor as det',
                    'est.idtipoestandarproveedor',
                    '=',
                    'det.idtipoestandarproveedor'
                )
                ->join(
                    'docsproveedortipoestandar as doc',
                    'det.iddetalletipoestandarproveedor',
                    '=',
                    'doc.iddetalletipoestandarproveedor'
                )
                ->select(
                    'doc.iddocsproveedortipoestandar',
                    'det.iddetalletipoestandarproveedor',
                    'det.idtipoestandarproveedor',
                    'det.detalle',
                    'doc.archivo',
                    'doc.estado_revision',
                    'doc.nombreDocumento',
                    'det.estado_trash',
                    'det.estado_delete',
                    'doc.idpersona'
                )
                ->where('doc.idpersona', $userId)   // 🔥 FILTRO REAL
                ->get();

            return ApiResponse::success($data, 'Tipos de estandar y documentos obtenidos');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

	  // listar tipos de estandar y documentos asociados
		public function select2_lista_sin_docs(Request $r, $iddetalle = null)
		{

			$userId = $r->user()->idpersona;

			// si viene por query string ?iddetalle=...
			$iddetalle = $r->input('iddetalle', $iddetalle);

      try {

        // Query base
        $query = DB::table('tipoestandarproveedor as est')
            ->join('detalletipoestandarproveedor as det', 'est.idtipoestandarproveedor', '=', 'det.idtipoestandarproveedor')
            ->join('persona as p', 'p.idtipoestandarproveedor', '=', 'est.idtipoestandarproveedor')
            ->leftJoin('docsproveedortipoestandar as doc', function ($join) use ($userId) {
                $join->on('doc.iddetalletipoestandarproveedor', '=', 'det.iddetalletipoestandarproveedor')
                     ->where('doc.idpersona', '=', $userId);
            })
            ->select(
                'det.iddetalletipoestandarproveedor',
                'det.detalle'
            )
            ->where('p.idpersona', $userId);

        if (filled($iddetalle)) {
            // ✅ Caso 2: llega iddetalle -> filtrar por ese id, aunque tenga doc
            $query->where('det.iddetalletipoestandarproveedor', (int)$iddetalle);
        } else {
            // ✅ Caso 1: NO llega iddetalle -> solo los que NO tienen doc
            $query->whereNull('doc.iddocsproveedortipoestandar');
        }

        $data = $query->distinct()
            ->orderBy('det.detalle')
            ->get();

        // Opciones HTML
        $options = '';
        foreach ($data as $t) {
            $options .= '<option value="'.$t->iddetalletipoestandarproveedor.'">'.e($t->detalle).'</option>';
        }

				return ApiResponse::success($options, 'Tipos estándar sin documentos obtenidos');

			} catch (\Throwable $e) {
					return ApiResponse::error($e);
			}
		}
    
}
