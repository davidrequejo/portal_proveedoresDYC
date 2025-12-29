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
						$doc->idpersona = $r->user()->id;
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
		
		// Mostrar 
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
			 $userId = $r->user()->id;   // usuario logueado

        try {
            //$id = $r->input('idtipoestandarproveedor');

        // Crear la consulta base
          $data = DB::table('tipoestandarproveedor as est')
            ->join('detalletipoestandarproveedor as det', 'est.idtipoestandarproveedor', '=', 'det.idtipoestandarproveedor')
            ->join('persona as p', 'p.idtipoestandarproveedor', '=', 'est.idtipoestandarproveedor')
            ->Join('docsproveedortipoestandar as doc', 'det.iddetalletipoestandarproveedor', '=', 'doc.iddetalletipoestandarproveedor')
            ->select(
                'doc.iddocsproveedortipoestandar',
                'det.iddetalletipoestandarproveedor',
                'det.idtipoestandarproveedor',
                'det.detalle',                
								'doc.archivo',
								'doc.estado_revision', 
								'doc.nombreDocumento', 
								'det.estado_trash',
                'det.estado_delete'
            )
            ->where('p.idpersona', $userId) // Filtrar por el ID del usuario logueado
                  ->get()
            ->map(function ($row) {
                return [
                'iddocsproveedortipoestandar' => $row->iddocsproveedortipoestandar,
                'iddetalletipoestandarproveedor' => $row->iddetalletipoestandarproveedor,
                'idtipoestandarproveedor'        => $row->idtipoestandarproveedor,
                'detalle'                        => $row->detalle,
                'archivo'                        => $row->archivo,
                'estado_revision'                => $row->estado_revision,
                'nombreDocumento'                => $row->nombreDocumento,
                'estado_trash'                   => $row->estado_trash,
                'estado_delete'                  => $row->estado_delete
                ];
            });

        return ApiResponse::success($data, 'Tipos de estandar y documentos obtenidos');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }

        
    }

	  // listar tipos de estandar y documentos asociados
    public function select2_lista_sin_docs(Request $r, )
    {
			 $userId = $r->user()->id;   // usuario logueado
       $options = ''; // string para concatenar HTML
        try {           

						$data = DB::table('tipoestandarproveedor as est')
								->join('detalletipoestandarproveedor as det', 'est.idtipoestandarproveedor', '=', 'det.idtipoestandarproveedor')
								->join('persona as p', 'p.idtipoestandarproveedor', '=', 'est.idtipoestandarproveedor')

								// 👇 LEFT JOIN para poder detectar los que NO existen en doc
								->leftJoin('docsproveedortipoestandar as doc', function ($join) use ($userId) {
										$join->on('doc.iddetalletipoestandarproveedor', '=', 'det.iddetalletipoestandarproveedor')
												->where('doc.idpersona', '=', $userId); // importante: comparar contra el usuario
								})

								->select(
										'det.iddetalletipoestandarproveedor',
										'det.idtipoestandarproveedor',
										'det.detalle',
										'det.estado_trash',
										'det.estado_delete'
								)
								->where('p.idpersona', $userId)                 // usuario logueado
								->whereNull('doc.iddocsproveedortipoestandar')  // ✅ solo los que NO tienen doc
								->orderBy('det.detalle')
								->get();

              
							foreach ($data as $t) {
									$options .= '<option value="'.$t->iddetalletipoestandarproveedor.'" >' . e($t->detalle). '</option>';
							}

            return ApiResponse::success($options, 'Tipo Estandar obtenida');


        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }

        
    }
    
}
