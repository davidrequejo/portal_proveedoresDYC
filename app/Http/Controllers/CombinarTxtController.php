<?php

namespace App\Http\Controllers;

use App\Models\CombinarTxt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CombinarTxtController extends Controller
{
    public function index()
    {
       return view('combinar_txt');
    }

    // Guardar los archivos subidos
    public function guardar_txt(Request $request)
    {
        $request->validate([
            'archivo_txt.*' => 'required|file'
        ]);

        $archivos = $request->file('archivo_txt');
        $datos_guardados = [];

        // Eliminar todos los registros antes de guardar los nuevos datos
        
        DB::table('archivos_data')->delete();



        // Guardar los archivos
        foreach ($archivos as $archivo) {
            // Obtener el nombre original del archivo
            $nombre_archivo = $archivo->getClientOriginalName();
            
            // Almacenar el archivo
            $archivo->storeAs('archivos/', $nombre_archivo, 'public');

            // Leer el contenido del archivo
            $contenido = file($archivo->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // Procesar cada línea
            foreach ($contenido as $linea) {
                $partes = explode('|', trim($linea));

                if (count($partes) >= 5) {
                    $datos_guardados[] = [
                        'codalterno' => trim($partes[0]),
                        'dni' =>        trim($partes[1]),
                        'codbase' =>    trim($partes[2]),
                        'monto1' =>     isset( $partes[3]) ? (is_numeric($partes[3]) ? floatval($partes[3]) : null) : null ,
                        'monto2' =>     isset( $partes[4]) ? (is_numeric($partes[4]) ? floatval($partes[4]) : null) : null ,
                        'monto3' =>     isset( $partes[5]) ? (is_numeric($partes[5]) ? floatval($partes[5]) : null) : null ,
                        'nombre_archivo' => $nombre_archivo
                    ];
                }
            }
        }

        // Insertar en lotes para evitar el límite de parámetros de PostgreSQL
        $batchSize = 1000; // Tamaño del lote (puedes ajustarlo)
        $chunks = array_chunk($datos_guardados, $batchSize);

        foreach ($chunks as $chunk) {
            CombinarTxt::insert($chunk); // Insertar cada bloque de datos
        }

        return response()->json(['status' => true, 'message' => 'Archivos guardados correctamente']);
    }


    // Mostrar los datos guardados
    public function mostrar_lista()
    {
        try {
            // Obtenemos el primer archivo registrado para determinar el tipo de archivo
            $primerArchivo = DB::table('archivos_data')
                ->select('nombre_archivo')
                ->first();

            // Si no se encuentra ningún archivo, retornamos un mensaje de error
            if (!$primerArchivo) {
                return response()->json(['error' => 'No se encontraron archivos.'], 400);
            }

            // Determinar el tipo de archivo del primer registro
            $tipo_archivo = '.' . strtolower(pathinfo($primerArchivo->nombre_archivo, PATHINFO_EXTENSION));

            // Iniciar la consulta base
            $query = DB::table('archivos_data')
                ->select(
                    'codalterno',
                    'dni',                
                    DB::raw('string_agg(DISTINCT nombre_archivo::text, \', \') as archivos') // Para mostrar los archivos asociados
                );

            // Condiciones según el tipo de archivo
            switch ($tipo_archivo) {
                case '.rem':
                    $query->addSelect('codbase')
                        ->addSelect(DB::raw('SUM(monto1) as monto1'))
                        ->addSelect(DB::raw('SUM(monto2) as monto2'))
                        ->groupBy('codalterno', 'dni', 'codbase');
                    break;

                case '.toc':
                    $query->addSelect('codbase')
                    ->addSelect('monto1', 'monto2', 'monto3')
                        ->groupBy('codalterno', 'dni', 'codbase', 'monto1', 'monto2', 'monto3');
                    break;

                case '.jor':
                    $query->addSelect(DB::raw('SUM(CAST(codbase AS numeric)) as codbase')) // Convertir codbase a número y sumarlo
                        ->addSelect(DB::raw('SUM(monto1) as monto1'))
                        ->addSelect(DB::raw('SUM(monto2) as monto2'))
                        ->addSelect(DB::raw('SUM(monto3) as monto3'))
                        ->groupBy('codalterno', 'dni');
                    break;

                case '.snl':
                    $query->addSelect('codbase')
                    ->addSelect(DB::raw('SUM(monto1) as monto1'))
                     ->where(function ($q) {
                            $q->where('codbase', '<>', '07')
                                ->orWhere('nombre_archivo', '<>', '060120250920614232278.snl');
                        })
                    ->groupBy('codalterno', 'dni', 'codbase');
                    break;

                default:
                    return response()->json(['error' => 'Tipo de archivo desconocido.'], 400);
            }


            // Ejecutamos la consulta con agrupación y ordenación, pero sin ordenar por 'codbase' en el caso de .jor
            if ($tipo_archivo !== '.jor') {
                $resultados = $query
                    ->orderBy('dni') // Ordenar por dni
                    ->orderBy('codbase') // Ordenar por codbase
                    ->get();
            } else {
                $resultados = $query
                    ->orderBy('dni') // Solo ordenar por 'dni' en el caso de .jor
                    ->get();
            }

            return response()->json($resultados);
        } catch (\Exception $e) {
            // Capturamos cualquier error y lo retornamos
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }






    // Descargar el archivo combinado en el mismo formato subido
   public function descargar_combinado($formato)
    {
        try {
            // Obtenemos el primer archivo registrado para determinar el tipo de archivo
            $primerArchivo = DB::table('archivos_data')
                ->select('nombre_archivo')
                ->first();

            // Si no se encuentra ningún archivo, retornamos un mensaje de error
            if (!$primerArchivo) {
                return response()->json(['error' => 'No se encontraron archivos.'], 400);
            }

            // Determinar el tipo de archivo del primer registro
            $tipo_archivo = '.' . strtolower(pathinfo($primerArchivo->nombre_archivo, PATHINFO_EXTENSION));

            // Iniciar la consulta base
            $query = DB::table('archivos_data')
                ->select(
                    'codalterno',
                    'dni',                
                    DB::raw('string_agg(DISTINCT nombre_archivo::text, \', \') as archivos') // Para mostrar los archivos asociados
                );

            // Condiciones según el tipo de archivo
            switch ($tipo_archivo) {
                case '.rem':
                    $query->addSelect('codbase')
                        ->addSelect(DB::raw('SUM(monto1) as monto1'))
                        ->addSelect(DB::raw('SUM(monto2) as monto2'))
                        ->groupBy('codalterno', 'dni', 'codbase');
                    break;

                case '.toc':
                    $query->addSelect('codbase','monto1', 'monto2', 'monto3')
                        ->groupBy('codalterno', 'dni', 'codbase', 'monto1', 'monto2', 'monto3');
                    break;

                case '.jor':
                    $query->addSelect(DB::raw('SUM(CAST(codbase AS numeric)) as codbase')) // Convertir codbase a número y sumarlo
                        ->addSelect(DB::raw('SUM(monto1) as monto1'))
                        ->addSelect(DB::raw('SUM(monto2) as monto2'))
                        ->addSelect(DB::raw('SUM(monto3) as monto3'))
                        ->groupBy('codalterno', 'dni');
                    break;

                case '.snl':
                    $query->addSelect('codbase')
                    ->addSelect(DB::raw('SUM(monto1) as monto1'))
                        ->groupBy('codalterno', 'dni', 'codbase');
                    break;

                default:
                    return response()->json(['error' => 'Tipo de archivo desconocido.'], 400);
            }


            // Ejecutamos la consulta con agrupación y ordenación, pero sin ordenar por 'codbase' en el caso de .jor
            if ($tipo_archivo !== '.jor') {
                $resultados = $query
                    ->orderBy('dni') // Ordenar por dni
                    ->orderBy('codbase') // Ordenar por codbase
                    ->get();
            } else {
                $resultados = $query
                    ->orderBy('dni') // Solo ordenar por 'dni' en el caso de .jor
                    ->get();
            }

            // Generar el contenido para descargar
            $contenido = '';
            foreach ($resultados as $resultado) {
                // Inicializamos la cadena para cada fila de datos
                $linea = $resultado->codalterno . '|' . $resultado->dni . '|' . $resultado->codbase . '|';

                // Concatenamos monto1, monto2 y monto3 solo si son válidos
                $linea .= (isset($resultado->monto1) ) ? floatval($resultado->monto1) . '|' : '';
                if ($tipo_archivo == '.toc') {
                    $linea .= '|';
                } else {
                    $linea .= (isset($resultado->monto2) ) ? ( $resultado->monto2 == null ? '|' : floatval($resultado->monto2) . '|') : '';
                }
                $linea .= (isset($resultado->monto3) ) ? floatval($resultado->monto3) . '|' : '';               

                // Añadimos esta línea al contenido final
                $contenido .= $linea . "\n"; // Añadir salto de línea después de cada fila
            }

            // Definir el nombre del archivo
            $nombre_archivo = 'archivo_'.substr($tipo_archivo, 1).'_combinado.' . $formato;

            // Descargar según el formato
            if ($formato == 'excel') {
                // Convertir el contenido a formato Excel (usamos `PHPExcel` o `Laravel Excel`)
                // Aquí sería necesario usar una librería como `maatwebsite/excel` para Laravel
                return response()->download($nombre_archivo, $contenido);
            } else {
                // Descargar como texto
                return response($contenido, 200)
                    ->header('Content-Type', 'text/plain')
                    ->header('Content-Disposition', 'attachment; filename="' . $nombre_archivo . '"');
            }
        } catch (\Exception $e) {
            // Capturamos cualquier error y lo retornamos
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
