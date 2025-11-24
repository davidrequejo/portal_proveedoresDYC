<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\ImportarHoraDetalleOberti;
use App\Models\ImportarHoraOberti;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportarHoraObertiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return view('importar-hora');
    }

    
    public function listCabeceras(Request $request)
    {
        try {
            // Parámetros de búsqueda y paginación
            $q        = trim((string) $request->input('q', ''));
            $perPage  = (int) $request->input('per_page', 10);
            $page     = (int) $request->input('page', 1);
            // si quieres permitir hasta 500:
            // $perPage  = max(1, min($perPage, 500));
            $perPage  = max(1, min($perPage, 100));

            $nombreArchivo = trim((string) $request->input('nombre_archivo', ''));
            $sheetName     = trim((string) $request->input('sheet_name', ''));
            $fechaDesde    = $request->input('created_from'); // 'YYYY-MM-DD'
            $fechaHasta    = $request->input('created_to');   // 'YYYY-MM-DD'

            // === Subquery de agregados por cabecera ===
            // (Si tus columnas ya son numéricas no necesitas ::numeric, pero así blindamos.)
            $agg = DB::table('registro_horas_detalle as d')
                ->selectRaw("
                    d.idregistro_horas,
                    COALESCE(SUM(d.lunes_hn    ::numeric),0) AS lunes_hn,
                    COALESCE(SUM(d.lunes_he    ::numeric),0) AS lunes_he,
                    COALESCE(SUM(d.martes_hn   ::numeric),0) AS martes_hn,
                    COALESCE(SUM(d.martes_he   ::numeric),0) AS martes_he,
                    COALESCE(SUM(d.miercoles_hn::numeric),0) AS miercoles_hn,
                    COALESCE(SUM(d.miercoles_he::numeric),0) AS miercoles_he,
                    COALESCE(SUM(d.jueves_hn   ::numeric),0) AS jueves_hn,
                    COALESCE(SUM(d.jueves_he   ::numeric),0) AS jueves_he,
                    COALESCE(SUM(d.viernes_hn  ::numeric),0) AS viernes_hn,
                    COALESCE(SUM(d.viernes_he  ::numeric),0) AS viernes_he,
                    COALESCE(SUM(d.sabado_hn   ::numeric),0) AS sabado_hn,
                    COALESCE(SUM(d.sabado_he   ::numeric),0) AS sabado_he,
                    COALESCE(SUM(d.domingo_hn  ::numeric),0) AS domingo_hn,
                    COALESCE(SUM(d.domingo_he  ::numeric),0) AS domingo_he,
                    COALESCE(SUM(d.total_dias_laborados),0)   AS total_dias_laborados,
                    COALESCE(SUM(d.total_horas_extras ::numeric),0) AS total_horas_extras
                ")
                ->groupBy('d.idregistro_horas');

            $query = \App\Models\ImportarHoraOberti::query()
                ->from('registro_horas as rh')
                // JOIN a los agregados
                ->leftJoinSub($agg, 'ag', function ($j) {
                    $j->on('rh.idregistro_horas', '=', 'ag.idregistro_horas');
                })
                ->select([
                    'rh.idregistro_horas',
                    'rh.nombre_archivo',
                    'rh.mime_type',
                    'rh.file_size',
                    'rh.sheet_index',
                    'rh.sheet_name',
                    'rh.total_filas',
                    'rh.filas_importadas',
                    'rh.created_at',

                    // Sumas HN/HE por día
                    DB::raw('COALESCE(ag.lunes_hn,0)     AS lunes_hn'),
                    DB::raw('COALESCE(ag.lunes_he,0)     AS lunes_he'),
                    DB::raw('COALESCE(ag.martes_hn,0)    AS martes_hn'),
                    DB::raw('COALESCE(ag.martes_he,0)    AS martes_he'),
                    DB::raw('COALESCE(ag.miercoles_hn,0) AS miercoles_hn'),
                    DB::raw('COALESCE(ag.miercoles_he,0) AS miercoles_he'),
                    DB::raw('COALESCE(ag.jueves_hn,0)    AS jueves_hn'),
                    DB::raw('COALESCE(ag.jueves_he,0)    AS jueves_he'),
                    DB::raw('COALESCE(ag.viernes_hn,0)   AS viernes_hn'),
                    DB::raw('COALESCE(ag.viernes_he,0)   AS viernes_he'),
                    DB::raw('COALESCE(ag.sabado_hn,0)    AS sabado_hn'),
                    DB::raw('COALESCE(ag.sabado_he,0)    AS sabado_he'),
                    DB::raw('COALESCE(ag.domingo_hn,0)   AS domingo_hn'),
                    DB::raw('COALESCE(ag.domingo_he,0)   AS domingo_he'),

                    // Totales de la semana (opcionales, útiles)
                    DB::raw('
                        ( COALESCE(ag.lunes_hn,0) + COALESCE(ag.martes_hn,0) + COALESCE(ag.miercoles_hn,0)
                        + COALESCE(ag.jueves_hn,0) + COALESCE(ag.viernes_hn,0) + COALESCE(ag.sabado_hn,0)
                        + COALESCE(ag.domingo_hn,0) ) AS hn_semana
                    '),
                    DB::raw('
                        ( COALESCE(ag.lunes_he,0) + COALESCE(ag.martes_he,0) + COALESCE(ag.miercoles_he,0)
                        + COALESCE(ag.jueves_he,0) + COALESCE(ag.viernes_he,0) + COALESCE(ag.sabado_he,0)
                        + COALESCE(ag.domingo_he,0) ) AS he_semana
                    '),

                    // Extras (si las llenas en detalle)
                    DB::raw('COALESCE(ag.total_dias_laborados,0)  AS total_dias_laborados'),
                    DB::raw('COALESCE(ag.total_horas_extras,0)    AS total_horas_extras'),
                ]);

            // Búsqueda general
            if ($q !== '') {
                $like = '%'.$q.'%';
                $query->where(function($w) use ($like) {
                    $w->where('rh.nombre_archivo', 'ilike', $like)
                    ->orWhere('rh.sheet_name', 'ilike', $like);
                    // ->orWhereRaw("to_char(rh.created_at, 'YYYY-MM-DD HH24:MI:SS') ilike ?", [$like]);
                });
            }

            // Filtros específicos
            if ($nombreArchivo !== '') {
                $query->where('rh.nombre_archivo', 'ilike', '%'.$nombreArchivo.'%');
            }
            if ($sheetName !== '') {
                $query->where('rh.sheet_name', 'ilike', '%'.$sheetName.'%');
            }
            if ($fechaDesde) {
                $query->whereDate('rh.created_at', '>=', $fechaDesde);
            }
            if ($fechaHasta) {
                $query->whereDate('rh.created_at', '<=', $fechaHasta);
            }

            // Orden (recientes primero)
            $query->orderByDesc('rh.created_at')->orderByDesc('rh.idregistro_horas');

            // Paginación
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            // Salida
            $items = collect($paginator->items())->map(function ($row) {
                return [
                    'idregistro_horas' => $row->idregistro_horas,
                    'nombre_archivo'   => $row->nombre_archivo,
                    'sheet_name'       => $row->sheet_name,
                    'sheet_index'      => $row->sheet_index,
                    'mime_type'        => $row->mime_type,
                    'file_size'        => (int) $row->file_size,
                    'total_filas'      => (int) $row->total_filas,
                    'filas_importadas' => (int) $row->filas_importadas,

                    // ← Agregados: HN/HE por día
                    'lunes_hn'         => (float) $row->lunes_hn,
                    'lunes_he'         => (float) $row->lunes_he,
                    'martes_hn'        => (float) $row->martes_hn,
                    'martes_he'        => (float) $row->martes_he,
                    'miercoles_hn'     => (float) $row->miercoles_hn,
                    'miercoles_he'     => (float) $row->miercoles_he,
                    'jueves_hn'        => (float) $row->jueves_hn,
                    'jueves_he'        => (float) $row->jueves_he,
                    'viernes_hn'       => (float) $row->viernes_hn,
                    'viernes_he'       => (float) $row->viernes_he,
                    'sabado_hn'        => (float) $row->sabado_hn,
                    'sabado_he'        => (float) $row->sabado_he,
                    'domingo_hn'       => (float) $row->domingo_hn,
                    'domingo_he'       => (float) $row->domingo_he,

                    // Totales útiles
                    'hn_semana'        => (float) $row->hn_semana,
                    'he_semana'        => (float) $row->he_semana,
                    'total_dias_laborados' => (int) $row->total_dias_laborados,
                    'total_horas_extras'   => (float) $row->total_horas_extras,

                    'created_at'       => optional($row->created_at)->format('d/m/Y h:i:s A'),
                ];
            })->all();

            return \App\Helpers\ApiResponse::success([
                'items' => $items,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                    'has_more'     => $paginator->hasMorePages(),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return \App\Helpers\ApiResponse::validation($ve->errors());
        } catch (\Throwable $e) {
            return \App\Helpers\ApiResponse::error($e, 500);
        }
    }

    // === NUEVO: previsualización ===
    public function preview(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB
            'sheet_index' => 'nullable|integer|min:0'
        ], [], ['archivo_excel' => 'Archivo Excel']);

        $file = $request->file('archivo_excel');

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheetNames  = $spreadsheet->getSheetNames();
        $sheetIndex  = $request->integer('sheet_index', 0);

        if ($sheetIndex < 0 || $sheetIndex >= count($sheetNames)) {
            $sheetIndex = 0;
        }
        $sheet = $spreadsheet->getSheet($sheetIndex);

        // Mapeo de columnas (tu plantilla)
        $COLS = [
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W'
        ];

        $headerRow = 6;   // tu cabecera visible
        $dataStart = 7;   // primeras filas de datos
        $previewRows = 10;

        // Leer encabezados de la fila 6 (aunque haya celdas combinadas, leemos A6..W6)
        $headers = [];
        foreach ($COLS as $col) {
            $cell = $sheet->getCell($col.$headerRow);
            $raw  = $cell->getValue();

            if (ExcelDate::isDateTime($cell) && is_numeric($raw)) {
                // Conviertes el serial a DateTime y lo formateas a tu gusto
                $dt = ExcelDate::excelToDateTimeObject($raw);
                $val = $dt->format('d/m/y'); // o 'd/m/Y' si quieres 4 dígitos
            } else {
                // Para no-fecha, respeta fórmulas
                $val = trim((string)$cell->getCalculatedValue());
            }

            $headers[] = ($val !== '') ? $val : $col; // fallback a letra de columna
        }

        // Leer 10 filas desde la 7
        $rows = [];
        for ($r = $dataStart; $r < $dataStart + $previewRows; $r++) {
            $rowVals = [];
            $isEmpty = true;
            foreach ($COLS as $col) {
                $v = $sheet->getCell($col.$r)->getCalculatedValue();
                // Normaliza strings
                if (is_string($v)) $v = trim($v);
                if ($v !== null && $v !== '') $isEmpty = false;
                $rowVals[] = $v;
            }
            if ($isEmpty) break;
            $rows[] = $rowVals;
        }

        return response()->json([
            'status'      => true,
            'sheet_names' => $sheetNames,
            'sheet_index' => $sheetIndex,
            'headers'     => $headers,
            'rows'        => $rows,
        ]);
    }
    
    // --- IMPORTACIÓN (crea CABECERA y luego DETALLE en bulk)
    public function import(Request $request)
    {
        try {
            $request->validate([
                'archivo_excel'  => 'required|file|mimes:xlsx,xls|max:10240',
                'sheet_index'    => 'required|integer|min:0',                
                'nombre_proyecto' => 'nullable|string',
                'partida_control' => 'nullable|string',
                'concepto'        => 'nullable|string',
            ], [], ['archivo_excel' => 'Archivo Excel']);

            $file          = $request->file('archivo_excel');            
            $sheetIndex    = (int)$request->input('sheet_index');

            $proyecto      = $request->input('nombre_proyecto');
            $partida       = $request->input('partida_control');
            $concepto      = $request->input('concepto');

            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheetNames  = $spreadsheet->getSheetNames();
            if ($sheetIndex >= count($sheetNames)) $sheetIndex = 0;
            $sheet       = $spreadsheet->getSheet($sheetIndex);
            $sheetName   = $sheetNames[$sheetIndex] ?? null;

            // 1) Crear CABECERA con metadatos
            $cabecera = ImportarHoraOberti::create([
                'nombre_archivo'  => $file->getClientOriginalName(),
                'mime_type'       => $file->getClientMimeType(),
                'file_size'       => $file->getSize(),
                'sheet_index'     => $sheetIndex,
                'sheet_name'      => $sheetName,
                'created_at'      => now(),
            ]);            

            // 2) Parseo columnas
            $COL = [
                'NRO' => 'A',
                'APELLIDOS_NOMBRES' => 'B',
                'DNI'           => 'C',
                'CARGO'         => 'D',
                'FECHA_INGRESO' => 'E',
                'HIJOS'         => 'F',
                'LUNES_HN'      => 'G',     'LUNES_HE' => 'H',
                'MARTES_HN'     => 'I',     'MARTES_HE' => 'J',
                'MIERCOLES_HN'  => 'K',     'MIERCOLES_HE' => 'L',
                'JUEVES_HN'     => 'M',     'JUEVES_HE' => 'N',
                'VIERNES_HN'    => 'O',     'VIERNES_HE' => 'P',
                'SABADO_HN'     => 'Q',     'SABADO_HE' => 'R',
                'DOMINGO_HN'    => 'S',     'DOMINGO_HE' => 'T',
                'TOTAL_DIAS'    => 'U',
                'TOTAL_HE'      => 'V',
                'OBS'           => 'W',
            ];

            $parseHN = function($val) {
                $v = is_string($val) ? trim(mb_strtoupper($val)) : $val;
                return ($v === 'X') ? 8.5 : 0;
            };
            $parseHN_Sab = function($val) {
                $v = is_string($val) ? trim(mb_strtoupper($val)) : $val;
                return ($v === 'X') ? 5.5 : 0;
            };
            $parseHE = function($val) {
                if ($val === null) return 0;
                if (is_string($val)) $val = str_replace(',', '.', trim($val));
                return is_numeric($val) ? (float)$val : 0;
            };
            $toInt = fn($v) => is_numeric($v) ? (int)$v : 0;

            $bgHex = function(Worksheet $sheet, string $coord): ?string {
                $fill = $sheet->getStyle($coord)->getFill();
                if ($fill->getFillType() !== Fill::FILL_SOLID) return null;

                $argb = strtoupper($fill->getStartColor()->getARGB()); // ej: 'FFFF0000'
                $rgb  = strlen($argb) === 8 ? substr($argb, 2) : $argb; // quitar alpha si llega ARGB
                return (preg_match('/^[0-9A-F]{6}$/', $rgb)) ? ('#'.$rgb) : null;
            };

            $hexToRgb = function (?string $hex): ?array {
                if (!$hex) return null;                 // null -> sin color
                $h = ltrim($hex, '#');
                if (!preg_match('/^[0-9A-Fa-f]{6}$/', $h)) return null;
                return [
                    'r' => hexdec(substr($h, 0, 2)),
                    'g' => hexdec(substr($h, 2, 2)),
                    'b' => hexdec(substr($h, 4, 2)),
                ];
            };

            $rgbToHsv = function (int $r, int $g, int $b): array {
                $r /= 255; $g /= 255; $b /= 255;
                $max = max($r,$g,$b); $min = min($r,$g,$b); $d = $max - $min;

                // Hue
                if ($d == 0)      $h = 0;
                elseif ($max==$r) $h = 60 * fmod((($g-$b)/$d), 6);
                elseif ($max==$g) $h = 60 * ((($b-$r)/$d) + 2);
                else              $h = 60 * ((($r-$g)/$d) + 4);
                if ($h < 0) $h += 360;

                // Saturation & Value
                $s = ($max == 0) ? 0 : ($d / $max);
                $v = $max;

                return ['h'=>$h, 's'=>$s, 'v'=>$v];
            };

            
            /**
             * Clasifica un #RRGGBB a tu set de nombres:
             * Blanco, Negro, Amarillo, Naranja, Rojo, Rosa, Morado, Azul, Celeste, Verde, Marrón
             */
            $colorNameFromHex = function (?string $hex) use ($hexToRgb, $rgbToHsv): ?string {
                if (!$hex) return null;

                $rgb = $hexToRgb($hex);
                if (!$rgb) return null;

                ['h'=>$h, 's'=>$s, 'v'=>$v] = $rgbToHsv($rgb['r'], $rgb['g'], $rgb['b']);

                // 1) Neutros (gris/negro/blanco): decides según brillo
                if ($s <= 0.08) {
                    // Muy claros = Blanco; muy oscuros = Negro; intermedios: regla simple
                    if ($v >= 0.85) return 'Blanco';
                    if ($v <= 0.25) return 'Negro';
                    return $v >= 0.60 ? 'Blanco' : 'Negro';
                }
                // 2) Marrón (naranja oscuro)
                if ($h >= 10 && $h < 40 && $v < 0.65) return 'Marrón';
                // 3) Por rangos de tono (hue)                
                if ($h >= 345 || $h < 15) return 'Rojo';        // Rojo               
                if ($h >= 15 && $h < 40) return 'Naranja';      // Naranja                
                if ($h >= 40 && $h < 70) return 'Amarillo';     // Amarillo                
                if ($h >= 70 && $h < 170) {  return 'Verde';  } // Verde 
                // Celeste (azul claro/turquesa claro)
                if ($h >= 170 && $h < 195) {  return ($v >= 0.60) ? 'Celeste' : 'Azul';  }// Si es suficientemente claro, lo llamamos Celeste; si no, Azul               
                if ($h >= 195 && $h < 240) return 'Azul';       // Azul                
                if ($h >= 240 && $h < 300) return 'Morado';     // Morado (azul-violeta)
                // Rosa (magenta/rosas)
                if ($h >= 300 && $h < 345) { return ($v < 0.50) ? 'Morado' : 'Rosa';   }// Si está muy oscuro puede parecer morado
                
                return 'Negro'; // Fallback por si algo cae fuera (no debería):
            };

            // 3) Recorrer filas y armar DETALLE
            $rowsToInsert = [];
            $rowStart = 7; $currentRow = $rowStart; $maxScan = 20000; $emptyStreak = 0; $totalFilas = 0;

            while ($currentRow < $rowStart + $maxScan) {
                $nombres = trim((string)$sheet->getCell($COL['APELLIDOS_NOMBRES'].$currentRow)->getCalculatedValue());
                if ($nombres === '') {
                    $emptyStreak++;
                    if ($emptyStreak >= 3) break;
                    $currentRow++;
                    continue;
                }
                $emptyStreak = 0;
                $totalFilas++;

                $nro         = $sheet->getCell($COL['NRO'].$currentRow)->getCalculatedValue();
                // $dni         = trim((string)$sheet->getCell($COL['DNI'].$currentRow)->getCalculatedValue());
                // Usa getFormattedValue() para DNI
                $dniCell = $sheet->getCell($COL['DNI'].$currentRow);
                $dni     = trim((string) $dniCell->getFormattedValue());
                $cargo       = trim((string)$sheet->getCell($COL['CARGO'].$currentRow)->getCalculatedValue());
                $fIngresoRaw = $sheet->getCell($COL['FECHA_INGRESO'].$currentRow)->getValue();
                $hijos       = $sheet->getCell($COL['HIJOS'].$currentRow)->getCalculatedValue();

                $fechaIngreso = null;
                try {
                    $fechaIngreso = ExcelDate::isDateTime($sheet->getCell($COL['FECHA_INGRESO'].$currentRow))
                        ? Carbon::instance(ExcelDate::excelToDateTimeObject($fIngresoRaw))->toDateString()
                        : (trim((string)$fIngresoRaw) ? Carbon::parse($fIngresoRaw)->toDateString() : null);
                } catch (\Throwable $e) {
                    $fechaIngreso = null;
                }

                $lunes_hn     = $parseHN($sheet->getCell($COL['LUNES_HN'].$currentRow)->getCalculatedValue());
                $martes_hn    = $parseHN($sheet->getCell($COL['MARTES_HN'].$currentRow)->getCalculatedValue());
                $miercoles_hn = $parseHN($sheet->getCell($COL['MIERCOLES_HN'].$currentRow)->getCalculatedValue());
                $jueves_hn    = $parseHN($sheet->getCell($COL['JUEVES_HN'].$currentRow)->getCalculatedValue());
                $viernes_hn   = $parseHN($sheet->getCell($COL['VIERNES_HN'].$currentRow)->getCalculatedValue());
                $sabado_hn    = $parseHN_Sab($sheet->getCell($COL['SABADO_HN'].$currentRow)->getCalculatedValue());
                $domingo_hn   = $parseHN($sheet->getCell($COL['DOMINGO_HN'].$currentRow)->getCalculatedValue());

                $lunes_he     = $parseHE($sheet->getCell($COL['LUNES_HE'].$currentRow)->getCalculatedValue());
                $martes_he    = $parseHE($sheet->getCell($COL['MARTES_HE'].$currentRow)->getCalculatedValue());
                $miercoles_he = $parseHE($sheet->getCell($COL['MIERCOLES_HE'].$currentRow)->getCalculatedValue());
                $jueves_he    = $parseHE($sheet->getCell($COL['JUEVES_HE'].$currentRow)->getCalculatedValue());
                $viernes_he   = $parseHE($sheet->getCell($COL['VIERNES_HE'].$currentRow)->getCalculatedValue());
                $sabado_he    = $parseHE($sheet->getCell($COL['SABADO_HE'].$currentRow)->getCalculatedValue());
                $domingo_he   = $parseHE($sheet->getCell($COL['DOMINGO_HE'].$currentRow)->getCalculatedValue());

                // Colores de celda
                $lunes_hn_bg_color     = $bgHex($sheet, $COL['LUNES_HN'].$currentRow);
                $lunes_he_bg_color     = $bgHex($sheet, $COL['LUNES_HE'].$currentRow);

                $martes_hn_bg_color    = $bgHex($sheet, $COL['MARTES_HN'].$currentRow);
                $martes_he_bg_color    = $bgHex($sheet, $COL['MARTES_HE'].$currentRow);

                $miercoles_hn_bg_color = $bgHex($sheet, $COL['MIERCOLES_HN'].$currentRow);
                $miercoles_he_bg_color = $bgHex($sheet, $COL['MIERCOLES_HE'].$currentRow);

                $jueves_hn_bg_color    = $bgHex($sheet, $COL['JUEVES_HN'].$currentRow);
                $jueves_he_bg_color    = $bgHex($sheet, $COL['JUEVES_HE'].$currentRow);

                $viernes_hn_bg_color   = $bgHex($sheet, $COL['VIERNES_HN'].$currentRow);
                $viernes_he_bg_color   = $bgHex($sheet, $COL['VIERNES_HE'].$currentRow);

                $sabado_hn_bg_color    = $bgHex($sheet, $COL['SABADO_HN'].$currentRow);
                $sabado_he_bg_color    = $bgHex($sheet, $COL['SABADO_HE'].$currentRow);

                $domingo_hn_bg_color   = $bgHex($sheet, $COL['DOMINGO_HN'].$currentRow);
                $domingo_he_bg_color   = $bgHex($sheet, $COL['DOMINGO_HE'].$currentRow);

                // Nombres de color (según HEX)
                $lunes_hn_nombre_color     = $colorNameFromHex($lunes_hn_bg_color);
                $lunes_he_nombre_color     = $colorNameFromHex($lunes_he_bg_color);

                $martes_hn_nombre_color    = $colorNameFromHex($martes_hn_bg_color);
                $martes_he_nombre_color    = $colorNameFromHex($martes_he_bg_color);

                $miercoles_hn_nombre_color = $colorNameFromHex($miercoles_hn_bg_color);
                $miercoles_he_nombre_color = $colorNameFromHex($miercoles_he_bg_color);

                $jueves_hn_nombre_color    = $colorNameFromHex($jueves_hn_bg_color);
                $jueves_he_nombre_color    = $colorNameFromHex($jueves_he_bg_color);

                $viernes_hn_nombre_color   = $colorNameFromHex($viernes_hn_bg_color);
                $viernes_he_nombre_color   = $colorNameFromHex($viernes_he_bg_color);

                $sabado_hn_nombre_color    = $colorNameFromHex($sabado_hn_bg_color);
                $sabado_he_nombre_color    = $colorNameFromHex($sabado_he_bg_color);

                $domingo_hn_nombre_color   = $colorNameFromHex($domingo_hn_bg_color);
                $domingo_he_nombre_color   = $colorNameFromHex($domingo_he_bg_color);

                $total_dias_laborados = $toInt($sheet->getCell($COL['TOTAL_DIAS'].$currentRow)->getCalculatedValue());
                $total_horas_extras   = $parseHE($sheet->getCell($COL['TOTAL_HE'].$currentRow)->getCalculatedValue());
                $observaciones        = trim((string)$sheet->getCell($COL['OBS'].$currentRow)->getCalculatedValue());

                if (!$total_dias_laborados) {
                    $diasHN = [$lunes_hn,$martes_hn,$miercoles_hn,$jueves_hn,$viernes_hn,$sabado_hn,$domingo_hn];
                    $total_dias_laborados = collect($diasHN)->filter(fn($h)=>$h>0)->count();
                }
                if (!$total_horas_extras) {
                    $total_horas_extras = $lunes_he+$martes_he+$miercoles_he+$jueves_he+$viernes_he+$sabado_he+$domingo_he;
                }

                $rowsToInsert[] = [
                    'idregistro_horas' => $cabecera->idregistro_horas,
                    'periodo_inicio'   => now(),
                    'periodo_fin'      => now(),
                    'nro_hoja'         => is_numeric($nro) ? (int)$nro : null,
                    'apellidos_nombres'=> $nombres,
                    'dni'              => $dni,
                    'cargo'            => $cargo ?: null,
                    'fecha_ingreso'    => $fechaIngreso,
                    'hijos'            => is_numeric($hijos) ? (int)$hijos : null,

                    // Horas por celda
                    'lunes_hn'      =>$lunes_hn,    'lunes_he'      =>$lunes_he,
                    'martes_hn'     =>$martes_hn,   'martes_he'     =>$martes_he,
                    'miercoles_hn'  =>$miercoles_hn,'miercoles_he'  =>$miercoles_he,
                    'jueves_hn'     =>$jueves_hn,   'jueves_he'     =>$jueves_he,
                    'viernes_hn'    =>$viernes_hn,  'viernes_he'    =>$viernes_he,
                    'sabado_hn'     =>$sabado_hn,   'sabado_he'     =>$sabado_he,
                    'domingo_hn'    =>$domingo_hn,  'domingo_he'    =>$domingo_he,                    

                    // colores por celda (HEX y nombre)
                    'lunes_hn_bg_color'      => $lunes_hn_bg_color,     'lunes_hn_nombre_color' => $lunes_hn_nombre_color,
                    'lunes_he_bg_color'      => $lunes_he_bg_color,     'lunes_he_nombre_color' => $lunes_he_nombre_color,

                    'martes_hn_bg_color'     => $martes_hn_bg_color,    'martes_hn_nombre_color' => $martes_hn_nombre_color,
                    'martes_he_bg_color'     => $martes_he_bg_color,    'martes_he_nombre_color' => $martes_he_nombre_color,

                    'miercoles_hn_bg_color'  => $miercoles_hn_bg_color, 'miercoles_hn_nombre_color' => $miercoles_hn_nombre_color,
                    'miercoles_he_bg_color'  => $miercoles_he_bg_color, 'miercoles_he_nombre_color' => $miercoles_he_nombre_color,

                    'jueves_hn_bg_color'     => $jueves_hn_bg_color,    'jueves_hn_nombre_color' => $jueves_hn_nombre_color,
                    'jueves_he_bg_color'     => $jueves_he_bg_color,    'jueves_he_nombre_color' => $jueves_he_nombre_color,

                    'viernes_hn_bg_color'    => $viernes_hn_bg_color,   'viernes_hn_nombre_color' => $viernes_hn_nombre_color,
                    'viernes_he_bg_color'    => $viernes_he_bg_color,   'viernes_he_nombre_color' => $viernes_he_nombre_color,

                    'sabado_hn_bg_color'     => $sabado_hn_bg_color,    'sabado_hn_nombre_color' => $sabado_hn_nombre_color,
                    'sabado_he_bg_color'     => $sabado_he_bg_color,    'sabado_he_nombre_color' => $sabado_he_nombre_color,

                    'domingo_hn_bg_color'    => $domingo_hn_bg_color,   'domingo_hn_nombre_color' => $domingo_hn_nombre_color,
                    'domingo_he_bg_color'    => $domingo_he_bg_color,   'domingo_he_nombre_color' => $domingo_he_nombre_color,


                    'total_dias_laborados'  =>$total_dias_laborados,
                    'total_horas_extras'    =>$total_horas_extras,
                    'observaciones'         =>$observaciones ?: null,

                    'proyecto'        => $proyecto,
                    'partida_control' => $partida,
                    'concepto'        => $concepto,
                    'created_at'      => now(),
                ];

                $currentRow++;
            }

            if (empty($rowsToInsert)) {
                // si no hay detalle, borro la cabecera para no dejar basura
                $cabecera->delete();
                return ApiResponse::validation(['archivo_excel' => ['No se encontraron filas para importar.']]);
            }

            DB::beginTransaction();
            foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                ImportarHoraDetalleOberti::insert($chunk);
            }
            // actualizar totales cabecera
            $cabecera->update([
                'total_filas'      => $totalFilas,
                'filas_importadas' => count($rowsToInsert),
            ]);
            DB::commit();

            return ApiResponse::success([
                'idregistro_horas' => $cabecera->idregistro_horas,
                'sheet_name'       => $sheetName,                
                'nombre_archivo'   => $file->getClientOriginalName(),
                'rows'             => count($rowsToInsert)
            ], 'Importación exitosa');

        } catch (ValidationException $ve) {
            return ApiResponse::validation($ve->errors());
        } catch (\Throwable $e) {
            DB::rollBack();
            return ApiResponse::error($e, 500); // ojo: aquí corregí tu variable ($e, no $th)
        }
    }

    public function fillTemplate(Request $request)
    {
        try {
            $request->validate([
                'file_excel_plantilla'    => 'required|file|mimes:xlsx,xls|max:20480',
                'idregistro_horas' => 'required|integer|min:1',
                // opcionales con defaults
                'sheet_name'  => 'nullable|string',   // default: TAREO
                'row_start'   => 'nullable|integer',  // default: 8
                'dni_col'     => 'nullable|string',   // default: C
            ], [], ['file_excel_plantilla' => 'Plantilla Excel']);

            $file               = $request->file('file_excel_plantilla');
            $idCab              = (int)$request->input('idregistro_horas');
            $sheetName          = $request->input('sheet_name') ?: 'TAREO';
            $rowStart           = (int)($request->input('row_start') ?: 8);
            $dniCol             = strtoupper($request->input('dni_col') ?: 'C');
            $fillOnlyEmpty      = $request->boolean('rellenar_solo_celdas_vacias'); 
            $no_tarear_verde    = $request->boolean('no_tarear_verde');

            $sql_tarear_color = "";

            if ($no_tarear_verde) {
                $sql_tarear_color = " /* Lunes */
                    COALESCE(SUM(CASE WHEN lunes_hn_nombre_color = 'Verde' THEN 0 ELSE COALESCE(lunes_hn,0) END),0) + 
                    COALESCE(SUM(CASE WHEN lunes_he_nombre_color = 'Verde' THEN 0 ELSE COALESCE(lunes_he,0) END),0) AS lun,

                    /* Martes */
                    COALESCE(SUM(CASE WHEN martes_hn_nombre_color = 'Verde' THEN 0 ELSE COALESCE(martes_hn,0) END),0) +
                    COALESCE(SUM(CASE WHEN martes_he_nombre_color = 'Verde' THEN 0 ELSE COALESCE(martes_he,0) END),0) AS mar,

                    /* Miércoles */
                    COALESCE(SUM(CASE WHEN miercoles_hn_nombre_color = 'Verde' THEN 0 ELSE COALESCE(miercoles_hn,0) END),0) +
                    COALESCE(SUM(CASE WHEN miercoles_he_nombre_color = 'Verde' THEN 0 ELSE COALESCE(miercoles_he,0) END),0) AS mier,

                    /* Jueves */
                    COALESCE(SUM(CASE WHEN jueves_hn_nombre_color = 'Verde' THEN 0 ELSE COALESCE(jueves_hn,0) END),0) +
                    COALESCE(SUM(CASE WHEN jueves_he_nombre_color = 'Verde' THEN 0 ELSE COALESCE(jueves_he,0) END),0) AS jue,

                    /* Viernes */
                    COALESCE(SUM(CASE WHEN viernes_hn_nombre_color = 'Verde' THEN 0 ELSE COALESCE(viernes_hn,0) END),0) +
                    COALESCE(SUM(CASE WHEN viernes_he_nombre_color = 'Verde' THEN 0 ELSE COALESCE(viernes_he,0) END),0) AS vie,

                    /* Sábado */
                    COALESCE(SUM(CASE WHEN sabado_hn_nombre_color = 'Verde' THEN 0 ELSE COALESCE(sabado_hn,0) END),0) +
                    COALESCE(SUM(CASE WHEN sabado_he_nombre_color = 'Verde' THEN 0 ELSE COALESCE(sabado_he,0) END),0) AS sab,

                    /* Domingo */
                    COALESCE(SUM(CASE WHEN domingo_hn_nombre_color = 'Verde' THEN 0 ELSE COALESCE(domingo_hn,0) END),0) +
                    COALESCE(SUM(CASE WHEN domingo_he_nombre_color = 'Verde' THEN 0 ELSE COALESCE(domingo_he,0) END),0) AS dom,";
            } else {
                $sql_tarear_color = "COALESCE(SUM(lunes_hn       + lunes_he),0)      AS lun,
                    COALESCE(SUM(martes_hn      + martes_he),0)     AS mar,
                    COALESCE(SUM(miercoles_hn   + miercoles_he),0)  AS mier,
                    COALESCE(SUM(jueves_hn      + jueves_he),0)     AS jue,
                    COALESCE(SUM(viernes_hn     + viernes_he),0)    AS vie,
                    COALESCE(SUM(sabado_hn      + sabado_he),0)     AS sab,
                    COALESCE(SUM(domingo_hn     + domingo_he),0)    AS dom,";
            }
            

            // 1) Agregar horas por DNI (suma HN+HE por día)
            $rows = ImportarHoraDetalleOberti::query()
                ->selectRaw("
                    dni,
                    MAX(proyecto)        AS proyecto,
                    MAX(partida_control) AS partida_control,
                    MAX(concepto)        AS concepto,

                    $sql_tarear_color  

                    /* HEX a pintar por día si hay 'Verde' en HE u HN (prioridad HE) */
                    COALESCE(
                        MAX(CASE WHEN lunes_he_nombre_color = 'Verde' THEN lunes_he_bg_color END),
                        MAX(CASE WHEN lunes_hn_nombre_color = 'Verde' THEN lunes_hn_bg_color END)
                    ) AS lun_hex,

                    COALESCE(
                        MAX(CASE WHEN martes_he_nombre_color = 'Verde' THEN martes_he_bg_color END),
                        MAX(CASE WHEN martes_hn_nombre_color = 'Verde' THEN martes_hn_bg_color END)
                    ) AS mar_hex,

                    COALESCE(
                        MAX(CASE WHEN miercoles_he_nombre_color = 'Verde' THEN miercoles_he_bg_color END),
                        MAX(CASE WHEN miercoles_hn_nombre_color = 'Verde' THEN miercoles_hn_bg_color END)
                    ) AS mier_hex,

                    COALESCE(
                        MAX(CASE WHEN jueves_he_nombre_color = 'Verde' THEN jueves_he_bg_color END),
                        MAX(CASE WHEN jueves_hn_nombre_color = 'Verde' THEN jueves_hn_bg_color END)
                    ) AS jue_hex,

                    COALESCE(
                        MAX(CASE WHEN viernes_he_nombre_color = 'Verde' THEN viernes_he_bg_color END),
                        MAX(CASE WHEN viernes_hn_nombre_color = 'Verde' THEN viernes_hn_bg_color END)
                    ) AS vie_hex,

                    COALESCE(
                        MAX(CASE WHEN sabado_he_nombre_color = 'Verde' THEN sabado_he_bg_color END),
                        MAX(CASE WHEN sabado_hn_nombre_color = 'Verde' THEN sabado_hn_bg_color END)
                    ) AS sab_hex,

                    COALESCE(
                        MAX(CASE WHEN domingo_he_nombre_color = 'Verde' THEN domingo_he_bg_color END),
                        MAX(CASE WHEN domingo_hn_nombre_color = 'Verde' THEN domingo_hn_bg_color END)
                    ) AS dom_hex
                ")
                ->where('idregistro_horas', $idCab)
                ->groupBy('dni')
                ->get();

            $byDni = [];
            foreach ($rows as $r) {
                $byDni[trim((string)$r->dni)] = [
                    'proyecto'        => (string)$r->proyecto,
                    'partida_control' => (string)$r->partida_control,
                    'concepto'        => (string)$r->concepto,
                    'lun'  => (float)$r->lun,
                    'mar'  => (float)$r->mar,
                    'mier' => (float)$r->mier,
                    'jue'  => (float)$r->jue,
                    'vie'  => (float)$r->vie,
                    'sab'  => (float)$r->sab,
                    'dom'  => (float)$r->dom,

                    // nuevos: HEX a pintar si hubo “Verde”
                    'lun_hex' => (string)$r->lun_hex,
                    'mar_hex' => (string)$r->mar_hex,
                    'mier_hex'=> (string)$r->mier_hex,
                    'jue_hex' => (string)$r->jue_hex,
                    'vie_hex' => (string)$r->vie_hex,
                    'sab_hex' => (string)$r->sab_hex,
                    'dom_hex' => (string)$r->dom_hex,
                ];
            }

            // 2) Abrir plantilla (no se modifica el archivo original)
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getSheetByName($sheetName) ?: $spreadsheet->getActiveSheet();

            // 3) Columnas destino de columnas

            $COL_DEST = ['lun'=>'I','mar'=>'L','mier'=>'O','jue'=>'R','vie'=>'U','sab'=>'X','dom'=>'AA'];   // Horas            
            $COL_DEST_PROYECTO = 'E';                                                                       // Proyecto (una sola col)            
            $COL_DEST_PARTIDA = ['G','J','M','P','S','V'];                                                  // Partida_control (varias columnas)           
            $COL_DEST_CONCEPTO = ['H','K','N','Q','T','W','Z'];                                             // Concepto (varias columnas)

            // Orden canónico de días para alinear arreglos
            $DAYS_ORDER = array_keys($COL_DEST); // ['lun','mar','mier','jue','vie','sab','dom']

            // Construir mapas por día
            $COL_PARTIDA_BY_DAY  = [];
            $COL_CONCEPTO_BY_DAY = [];
            foreach ($DAYS_ORDER as $idx => $day) {
                if (isset($COL_DEST_PARTIDA[$idx]))  $COL_PARTIDA_BY_DAY[$day]  = $COL_DEST_PARTIDA[$idx];
                if (isset($COL_DEST_CONCEPTO[$idx])) $COL_CONCEPTO_BY_DAY[$day] = $COL_DEST_CONCEPTO[$idx];
            }

            // Helper: ¿la celda tiene contenido?
            $cellHasContent = function($sheet, string $coord): bool {
                $cell = $sheet->getCell($coord);
                // Si es fórmula, getCalculatedValue puede retornar '' cuando la fórmula da vacío
                $calc = $cell->getCalculatedValue();
                if ($calc !== null && $calc !== '') {
                    // si la evaluación da un 0, lo consideramos contenido (no vacío)
                    // nota: 0 == '0' -> contenido
                    return !(is_string($calc) && trim($calc) === '');
                }
                // Si no hay cálculo, miramos el valor crudo
                $raw = $cell->getValue();
                if (is_null($raw)) return false;
                if (is_string($raw)) return trim($raw) !== '';
                // números / fechas / bool -> contenido
                return true;
            };

            $applyBg = function($sheet, string $coord, ?string $hex): void {
                if (!$hex) return;
                $rgb = ltrim($hex, '#');
                if (strlen($rgb) !== 6) return;
                $sheet->getStyle($coord)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF' . strtoupper($rgb)); // alpha FF + RGB
            };

            $clearCell = function($sheet, string $coord): void {
                // Vacía la celda (sin cadena '0')
                $sheet->setCellValue($coord, null);
                // (Opcional) limpia el fondo si quieres quitar colores previos
                // $sheet->getStyle($coord)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            };


            // 4) Recorrer y rellenar
            $row = $rowStart;
            $emptyStreak = 0;
            $maxScan = 10000;

            while ($row < $rowStart + $maxScan) {
                $dni = trim((string)$sheet->getCell($dniCol.$row)->getCalculatedValue());
                if ($dni === '') {
                    $emptyStreak++;
                    if ($emptyStreak >= 5) break;
                    $row++;
                    continue;
                }
                $emptyStreak = 0;

                if (isset($byDni[$dni])) {

                    $h = $byDni[$dni];

                    // Proyecto
                    $coordProyecto = $COL_DEST_PROYECTO.$row;
                    if (!$fillOnlyEmpty || !$cellHasContent($sheet, $coordProyecto)) {
                        $sheet->setCellValueExplicit($coordProyecto, $h['proyecto'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }                   

                    // Horas + Partida/Concepto por día
                    foreach ($COL_DEST as $k => $colHoras) {
                        $coordHoras    = $colHoras.$row;
                        $coordPartida  = isset($COL_PARTIDA_BY_DAY[$k])  ? $COL_PARTIDA_BY_DAY[$k].$row  : null;
                        $coordConcepto = isset($COL_CONCEPTO_BY_DAY[$k]) ? $COL_CONCEPTO_BY_DAY[$k].$row : null;

                        $valor = (float)($h[$k] ?? 0);

                        // Pintar background si corresponde por “Verde”
                        $hexKey = $k . '_hex'; // p.e. 'lun_hex'
                        if (!empty($h[$hexKey])) {
                            $applyBg($sheet, $coordHoras, $h[$hexKey]);
                        }

                        // === Regla nueva: si el monto del día es 0, VACÍO en las tres celdas del día ===
                        if (abs($valor) < 1e-9) {
                            // Vaciar horas
                            $clearCell($sheet, $coordHoras);
                            // Vaciar Partida/Concepto del mismo día (si existen mapeos)
                            if ($coordPartida)  $clearCell($sheet, $coordPartida);
                            if ($coordConcepto) $clearCell($sheet, $coordConcepto);
                            // No pintamos fondo cuando es 0
                            continue;
                        }

                        // === Cuando hay valor (>0): escribir respetando "solo vacíos" ===
                        // 1) Horas
                        if (!$fillOnlyEmpty || !$cellHasContent($sheet, $coordHoras)) {
                            $sheet->setCellValueExplicit($coordHoras, $valor, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                            
                        }

                        // 2) Partida_control del día
                        if ($coordPartida && (!$fillOnlyEmpty || !$cellHasContent($sheet, $coordPartida))) {
                            $sheet->setCellValueExplicit($coordPartida, $h['partida_control'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }

                        // 3) Concepto del día
                        if ($coordConcepto && (!$fillOnlyEmpty || !$cellHasContent($sheet, $coordConcepto))) {
                            $sheet->setCellValueExplicit($coordConcepto, $h['concepto'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                    }


                   
                    
                }

                $row++;
            }

            // 5) Descargar
            // Detecta extensión del archivo subido
            $ext = strtolower($file->getClientOriginalExtension()); // 'xlsx' | 'xls' | 'xlsm'

            // Mapa de extensiones soportadas
            $map = [
                'xlsx' => [
                    'writer' => 'Xlsx',
                    'mime'   => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ],
                'xls' => [
                    'writer' => 'Xls',
                    'mime'   => 'application/vnd.ms-excel',
                ],
                'xlsm' => [
                    'writer' => 'Xlsx', // PhpSpreadsheet usa el mismo writer para xlsx y xlsm
                    'mime'   => 'application/vnd.ms-excel.sheet.macroEnabled.12',
                ],
            ];

            // Usa config según extensión (fallback a xlsx si no reconocido)
            $cfg = $map[$ext] ?? $map['xlsx'];

            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .'_Relleno_'.now()->format('Ymd_His').'.'.$ext;

            $writer = IOFactory::createWriter($spreadsheet, $cfg['writer']);

            return new StreamedResponse(function() use ($writer){
                $writer->save('php://output');
            }, 200, [
                'Content-Type'        => $cfg['mime'],
                'Content-Disposition' => 'attachment; filename="'.$name.'"',
                'Cache-Control'       => 'max-age=0, must-revalidate',
            ]);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return \App\Helpers\ApiResponse::validation($ve->errors());
        } catch (\Throwable $e) {
            return \App\Helpers\ApiResponse::error($e, 500);
        }
    }

    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $cabecera = \App\Models\ImportarHoraOberti::findOrFail($id);

                // 1) Borrar detalles primero
                // Si ImportarHoraDetalleOberti usa SoftDeletes:
                // $cabecera->detalles()->withTrashed()->forceDelete();
                $cabecera->detalles()->delete();

                // 2) Borrar cabecera
                $cabecera->delete();

                return \App\Helpers\ApiResponse::success(null, 'Registro eliminado correctamente');
            });
        } catch (ModelNotFoundException $e) {
            return \App\Helpers\ApiResponse::validation(['id' => ['No se encontró el registro.']]);
        } catch (\Throwable $e) {
            return \App\Helpers\ApiResponse::error($e, 500);
        }
    }


    public function mostrar_detalle_hora(Request $request)
    {
        try {
            // Parámetros
            $filtro_color   = (string) $request->get('filtro_color', '');
            $columna   = (string) $request->get('columna_detalle', 'apellidos_nombres');
            $buscar    = ((string) $request->get('buscar', ''));
            $perPage   = (int) $request->get('per_page', 10);
            $page      = (int) $request->get('page', 1);

            // Sanitizar per_page
            $perPage = max(1, min($perPage, 500));

            // Columnas permitidas para buscar
            $permitidas = ['dni', 'apellidos_nombres', 'idregistro_horas', 'nombre_archivo', 'observaciones'];
            if (!in_array($columna, $permitidas, true)) {
                $columna = 'apellidos_nombres';
            }


            // Mapa de sinónimos/variantes del combo a los valores que guardas en DB
            $mapColor = [
                'Verde'   => ['Verde'],
                'Amarillo'=> ['Amarillo'],
                'Naranja' => ['Naranja'],
                'Blanco'  => ['Blanco'],
                'Negro'   => ['Negro'],
                'Rojo'    => ['Rojo'],
                'Rosa'    => ['Rosa'],     
                'Morado'  => ['Morado'],
                'Azul'    => ['Azul'],
                'Celeste' => ['Celeste'],
                'Marrón'  => ['Marrón'],                
            ];

            // Columnas de nombre_color a filtrar
            $colsNombreColor = [
                'lunes_hn_nombre_color','lunes_he_nombre_color',
                'martes_hn_nombre_color','martes_he_nombre_color',
                'miercoles_hn_nombre_color','miercoles_he_nombre_color',
                'jueves_hn_nombre_color','jueves_he_nombre_color',
                'viernes_hn_nombre_color','viernes_he_nombre_color',
                'sabado_hn_nombre_color','sabado_he_nombre_color',
                'domingo_hn_nombre_color','domingo_he_nombre_color',
            ];

            // Operador LIKE sensible a motor (pgsql usa ILIKE para ser case-insensitive)
            $driver = DB::getDriverName();
            $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

            $query = \App\Models\ImportarHoraDetalleOberti::query()
                ->with(['cabecera:idregistro_horas,nombre_archivo'])
                ->select([
                    'idregistro_horas_detalle',
                    'idregistro_horas',
                    'nro_hoja',
                    'apellidos_nombres',
                    'dni',
                    // Hora por celda
                    'lunes_hn',     'lunes_he',
                    'martes_hn',    'martes_he',
                    'miercoles_hn', 'miercoles_he',
                    'jueves_hn',    'jueves_he',
                    'viernes_hn',   'viernes_he',
                    'sabado_hn',    'sabado_he',
                    'domingo_hn',   'domingo_he',
                    // Colores por celda (HN/HE) exactos
                    'lunes_hn_bg_color',    'lunes_he_bg_color',
                    'martes_hn_bg_color',   'martes_he_bg_color',
                    'miercoles_hn_bg_color','miercoles_he_bg_color',
                    'jueves_hn_bg_color',   'jueves_he_bg_color',
                    'viernes_hn_bg_color',  'viernes_he_bg_color',
                    'sabado_hn_bg_color',   'sabado_he_bg_color',
                    'domingo_hn_bg_color',  'domingo_he_bg_color',
                    // Nombres colores por celda (HN/HE) exactos
                    'lunes_hn_nombre_color',    'lunes_he_nombre_color',
                    'martes_hn_nombre_color',   'martes_he_nombre_color',
                    'miercoles_hn_nombre_color','miercoles_he_nombre_color',
                    'jueves_hn_nombre_color',   'jueves_he_nombre_color',
                    'viernes_hn_nombre_color',  'viernes_he_nombre_color',
                    'sabado_hn_nombre_color',   'sabado_he_nombre_color',
                    'domingo_hn_nombre_color',  'domingo_he_nombre_color',
                    'observaciones',
                ]);

            if ($buscar !== '') {
                if ($columna === 'idregistro_horas') {
                    // numérico → exacto
                    $query->where('idregistro_horas', (int) $buscar);

                } elseif ($columna === 'nombre_archivo') {
                    // nombre_archivo pertenece a la relación cabecera
                    $query->whereHas('cabecera', function ($q) use ($likeOp, $buscar) {
                        $q->where('nombre_archivo', $likeOp, "%{$buscar}%");
                    });
                    

                } else {
                    // columnas propias de registro_horas_detalle
                    $query->where($columna, $likeOp, "%{$buscar}%");
                }
            }


            if ($filtro_color !== '') {
                // Targets posibles según el mapa (si no existe en el mapa, usa el valor tal cual)
                $targets = $mapColor[$filtro_color] ?? [$filtro_color];

                $query->where(function ($q) use ($colsNombreColor, $targets, $driver, $likeOp) {
                    foreach ($colsNombreColor as $col) {
                        foreach ($targets as $t) {
                            // Como guardas valores exactos (e.g., 'Verde'), "=" es más eficiente.
                            // Si prefieres tolerancia a espacios/case, usa ILIKE/LIKE:
                            // $q->orWhere($col, $likeOp, $t);
                            $q->orWhere($col, '=', $t);
                        }
                    }
                });
            }

            // Orden reciente primero
            $query->orderBy('idregistro_horas_detalle', 'asc');

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            // Mapear items para el front
            $items = $paginator->getCollection()->map(function ($row) {
                return [
                    'idregistro_horas_detalle'  => $row->idregistro_horas_detalle,
                    'idregistro_horas'          => $row->idregistro_horas,
                    'nro_hoja'                  => $row->nro_hoja,
                    'apellidos_nombres'         => $row->apellidos_nombres,
                    'dni'                       => $row->dni,

                    'lunes_hn'                  => $row->lunes_hn,       'lunes_he'     => $row->lunes_he,
                    'martes_hn'                 => $row->martes_hn,      'martes_he'    => $row->martes_he,
                    'miercoles_hn'              => $row->miercoles_hn,   'miercoles_he' => $row->miercoles_he,
                    'jueves_hn'                 => $row->jueves_hn,      'jueves_he'    => $row->jueves_he,
                    'viernes_hn'                => $row->viernes_hn,     'viernes_he'   => $row->viernes_he,
                    'sabado_hn'                 => $row->sabado_hn,      'sabado_he'    => $row->sabado_he,
                    'domingo_hn'                => $row->domingo_hn,     'domingo_he'   => $row->domingo_he,

                    'lunes_hn_bg_color'         => $row->lunes_hn_bg_color,    'lunes_he_bg_color'      => $row->lunes_he_bg_color,
                    'martes_hn_bg_color'        => $row->martes_hn_bg_color,   'martes_he_bg_color'     => $row->martes_he_bg_color,
                    'miercoles_hn_bg_color'     => $row->miercoles_hn_bg_color,'miercoles_he_bg_color'  => $row->miercoles_he_bg_color,
                    'jueves_hn_bg_color'        => $row->jueves_hn_bg_color,   'jueves_he_bg_color'     => $row->jueves_he_bg_color,
                    'viernes_hn_bg_color'       => $row->viernes_hn_bg_color,  'viernes_he_bg_color'    => $row->viernes_he_bg_color,
                    'sabado_hn_bg_color'        => $row->sabado_hn_bg_color,   'sabado_he_bg_color'     => $row->sabado_he_bg_color,
                    'domingo_hn_bg_color'       => $row->domingo_hn_bg_color,  'domingo_he_bg_color'    => $row->domingo_he_bg_color,

                    'lunes_hn_nombre_color'     => $row->lunes_hn_nombre_color,    'lunes_he_nombre_color'      => $row->lunes_he_nombre_color,
                    'martes_hn_nombre_color'    => $row->martes_hn_nombre_color,   'martes_he_nombre_color'     => $row->martes_he_nombre_color,
                    'miercoles_hn_nombre_color' => $row->miercoles_hn_nombre_color,'miercoles_he_nombre_color'  => $row->miercoles_he_nombre_color,
                    'jueves_hn_nombre_color'    => $row->jueves_hn_nombre_color,   'jueves_he_nombre_color'     => $row->jueves_he_nombre_color,
                    'viernes_hn_nombre_color'   => $row->viernes_hn_nombre_color,  'viernes_he_nombre_color'    => $row->viernes_he_nombre_color,
                    'sabado_hn_nombre_color'    => $row->sabado_hn_nombre_color,   'sabado_he_nombre_color'     => $row->sabado_he_nombre_color,
                    'domingo_hn_nombre_color'   => $row->domingo_hn_nombre_color,  'domingo_he_nombre_color'    => $row->domingo_he_nombre_color,

                    'observaciones'             => $row->observaciones,
                    'nombre_archivo'            => optional($row->cabecera)->nombre_archivo,
                ];
            })->values();

            $meta = [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem() ?? 0,
                'to'           => $paginator->lastItem() ?? 0,
            ];

            return \App\Helpers\ApiResponse::success([
                'items' => $items,
                'meta'  => $meta,
            ]);
        } catch (\Throwable $e) {
            return \App\Helpers\ApiResponse::error($e, 500);
        }
    }


    


}
