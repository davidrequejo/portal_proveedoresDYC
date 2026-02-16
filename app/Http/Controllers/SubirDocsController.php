<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocsProveedorTipoEstandar;
use App\Models\PersonaFechaHomologacion;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionDocsActualizadosClienteMail;

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
						$doc->idpersona_facha_homologacion = $r->input('idpersona_facha_homologacion', $doc->idpersona_facha_homologacion);

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
								$doc->estado_revision = 'Actualizado';
						}

						// 4) Guardar cambios
						$doc->save();

						//despues de guardar quiero verfificar si todo los ducumentos estandar han sido actualizados
						$idpersona_facha_homologacion = $doc->idpersona_facha_homologacion;
						$idpersona = $doc->idpersona;	
						$existe_vacio = DB::table('docsproveedortipoestandar as dpt')
							->join( 'persona_facha_homologacion as pfh', 'dpt.idpersona_facha_homologacion', '=', 'pfh.idpersona_facha_homologacion' )
							->join( 'detalletipoestandarproveedor as dtp', 'dpt.iddetalletipoestandarproveedor', '=', 'dtp.iddetalletipoestandarproveedor' )
							->join( 'documento_tipo_estandar as destts', 'destts.iddocumento_tipo_estandar', '=', 'dtp.iddocumento_tipo_estandar' )
							->where('dpt.idpersona', $idpersona)
							->where('dpt.idpersona_facha_homologacion', $idpersona_facha_homologacion)
							->whereIn('destts.tipo_documento', ['Estandar', 'Modelo'])
							->where(function ($q) {
								$q->whereNull('dpt.archivo')
								->orWhere('dpt.archivo', '');
							})
							->exists();

						//$docs_completos = !$existe_vacio;
						if($existe_vacio){
							//todos los documentos estan completos
							return ApiResponse::success('Actualizado', 'Documento estandar actualizado correctamente');
						}else{
							//deseo traer el nombre de la persona 
							$persona = Proveedor::where('idpersona', $doc->idpersona)->first();
							$cliente_proveedor = $persona->nombre_razonsocial;

							//necesito consulta de los compradores
							/** Enviar correo de notificación a logística */
							$logistica = DB::table('persona')
							->join('tipo_persona', 'persona.idtipo_persona', '=', 'tipo_persona.idtipo_persona')
							->where('persona.idtipo_persona', 6)
							->where('persona.estado', 1)
							->where('persona.estado_delete', 1)
							->select('persona.idpersona', 'persona.nombre_razonsocial', 'persona.email', 'tipo_persona.descripcion')
							->get();

							foreach ($logistica as $usuarioLogistica) {

								// Enviar el correo con los datos adecuados
								Mail::to($usuarioLogistica->email)->queue(new NotificacionDocsActualizadosClienteMail($cliente_proveedor) );
							}  

							return ApiResponse::success('Actualizado', 'Documento estándar actualizado correctamente');        

						}

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

		public function periodo_homologacion_xpersona(Request $r)
    {        
        try {
            $idpersona = $r->user()->idpersona;

            $data =  DB::table('persona_facha_homologacion')
            ->select(
                'idpersona_facha_homologacion',
                'descripcion',
                'fecha_inicio_proceso',
                'fecha_fin',
                'fecha_inicio_periodo_h',
                'fecha_fin_periodo_h',
                'estado_homologacion',
                'estado_trash'
            )
            ->where('estado_trash', '1')
            ->where('estado_delete', '1')
            ->where('idpersona', $idpersona)
            ->get();

            return ApiResponse::success($data, 'Cateogria de proveedores obtenidos');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

	  // listar para tabla tipos de estandar y documentos asociados
    public function listar_docs_tipos_est_xuser(Request $r)
    {
        $idPeriodo = $r->input('idPeriodo');
        $idpersona = $r->user()->idpersona; // usuario logueado

        try {

            $data = DB::table('docsproveedortipoestandar as docs')
                ->join( 'persona_facha_homologacion as pfh', 'docs.idpersona_facha_homologacion', '=', 'pfh.idpersona_facha_homologacion' )
                ->join( 'detalletipoestandarproveedor as dtp', 'docs.iddetalletipoestandarproveedor', '=', 'dtp.iddetalletipoestandarproveedor' )
                ->join( 'documento_tipo_estandar as destts', 'destts.iddocumento_tipo_estandar', '=', 'dtp.iddocumento_tipo_estandar' )
                ->select(
                    'destts.descripcion',
                    'destts.tipo_documento',
                    'destts.archivo as archivo_modelo',
                    'docs.iddocsproveedortipoestandar',
                    'docs.idpersona',
                    'docs.nombreDocumento',
                    'docs.archivo', 
                    'docs.observacion', 
                    'docs.estado_revision',
                    'docs.estado_trash',
                    'docs.estado_delete'
                )
                ->where('pfh.idpersona_facha_homologacion', $idPeriodo)
				        ->whereIn('destts.tipo_documento', ['Estandar', 'Modelo'])
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




    public function descargar_documento_estandar($iddocsproveedortipoestandar)
    {
        try {
            $doc = DocsProveedorTipoEstandar::findOrFail($iddocsproveedortipoestandar);

            $filePath = public_path($doc->archivo); // ruta completa

            if (is_file($filePath)) {
                return response()->download($filePath, $doc->nombreDocumento);
            } else {
                return abort(404, 'Archivo no encontrado');
            }
        } catch (\Throwable $e) {
            return abort(500, 'Error al descargar el archivo');
        }
    }
    
}
