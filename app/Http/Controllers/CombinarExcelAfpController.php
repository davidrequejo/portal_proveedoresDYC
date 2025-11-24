<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\CombinarExcelAfp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CombinarExcelAfpController extends Controller
{
    public function index()
    {
        return view('combinar_excel_afp');
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'archivo_excel'   => 'required',
                'archivo_excel.*' => 'file|mimes:xlsx,xls,csv|max:20480',
            ]);

            $files = $request->file('archivo_excel');
            if (!is_array($files)) $files = [$files];

            $total   = 0;
            $resumen = [];

            // Mapa fijo por letra (mismo orden que tu BD)
            $map = [
                'A' => 'n_secuencia',
                'B' => 'cuspp',
                'C' => 'tipo_documento',
                'D' => 'n_documento',
                'E' => 'apellido_paterno',
                'F' => 'apellido_materno',
                'G' => 'nombres',
                'H' => 'relacion_laboral',
                'I' => 'inicio_relacion_laboral',
                'J' => 'cese_relacion_laboral',
                'K' => 'excepcion_de_aportar',
                'L' => 'rem_asegurable',
                'M' => 'aporte_con_fin',
                'N' => 'aporte_sin_fin',
                'O' => 'aporte_empleador',
                'P' => 'rubro_trabajador',
                'Q' => 'afp',
            ];
            $letters = array_keys($map);

            // Eliminar todos los registros antes de guardar los nuevos datos
            
            DB::table('planilla_excel_afp')->delete(); // elimina todas las filas


            foreach ($files as $file) {
                $nombre = $file->getClientOriginalName();

                $spread = IOFactory::load($file->getRealPath());
                $sheet  = $spread->getActiveSheet();

                // A,B,C... con fila 1 = (cabeceras / primera fila). Trabajaremos desde la 2.
                $rows = $sheet->toArray(null, true, true, true);
                if (count($rows) < 2) {
                    $resumen[] = ['archivo' => $nombre, 'insertadas' => 0];
                    $spread->disconnectWorksheets();
                    unset($spread);
                    continue;
                }

                // Si tu archivo SIEMPRE tiene cabecera en la fila 1, la saltamos:
                unset($rows[1]);

                $batch       = [];
                $chunk       = 500;
                $insertadas  = 0;
                $now         = now();

                foreach ($rows as $r) {
                    // Fila completamente vacía -> saltar
                    $isEmpty = true;
                    foreach ($letters as $L) {
                        if (isset($r[$L]) && trim((string)$r[$L]) !== '') {
                            $isEmpty = false;
                            break;
                        }
                    }
                    if ($isEmpty) continue;

                    // Construir registro respetando el orden del $map
                    $registro = [];
                    foreach ($map as $L => $campo) {
                        $val = $r[$L] ?? null;

                        switch ($campo) {
                            case 'inicio_relacion_laboral':
                            case 'cese_relacion_laboral':
                                $registro[$campo] = self::toStr($val);
                                break;

                            case 'rem_asegurable':
                            case 'aporte_con_fin':
                            case 'aporte_sin_fin':
                            case 'aporte_empleador':
                                $registro[$campo] = self::toFloat($val);
                                break;

                            default:
                                $registro[$campo] = self::toStr($val);
                        }
                    }

                    // Meta
                    $registro['archivo_nombre'] = $nombre;
                    $registro['created_at']     = $now;

                    $batch[] = $registro;

                    if (count($batch) >= $chunk) {
                        DB::table('planilla_excel_afp')->insert($batch);
                        $insertadas += count($batch);
                        $batch = [];
                    }
                }

                if ($batch) {
                    DB::table('planilla_excel_afp')->insert($batch);
                    $insertadas += count($batch);
                    $batch = [];
                }

                $total += $insertadas;
                $resumen[] = ['archivo' => $nombre, 'insertadas' => $insertadas];

                $spread->disconnectWorksheets();
                unset($spread);
            }

            return ApiResponse::success([
                'total_insertadas' => $total,
                'resumen'          => $resumen,
            ], 'Importación completada.');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    // ==== Helpers mínimos ====
    private static function toStr($v): ?string
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        return $s === '' ? null : $s;
    }

    private static function toFloat($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float)$v;
        $s = preg_replace('/[^\d,\.\-]/', '', (string)$v);
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif (strpos($s, ',') !== false) {
            $s = str_replace(',', '.', $s);
        }
        return is_numeric($s) ? (float)$s : null;
    }

    public function mostrar_lista(Request $request)
    {
        try {
            // Columnas base para agrupar
            $groupCols = [
                'n_secuencia',
                'cuspp',
                'tipo_documento',
                'n_documento',
                'apellido_paterno',
                'apellido_materno',
                'nombres',
                'aporte_con_fin',
                'aporte_sin_fin',
                'aporte_empleador',
                'rubro_trabajador',
                'afp'
            ];
            $selectCols = array_map(fn($c) => "p.$c", $groupCols);
            $groupByCols = implode(', ', $selectCols);

            $sql = "
            SELECT 
                $groupByCols,
                -- de la subconsulta lateral (valores más recientes)
                ult.relacion_laboral,
                ult.inicio_relacion_laboral,
                ult.cese_relacion_laboral,
                ult.excepcion_de_aportar,
                -- reglas de rem_asegurable
                CASE 
                    WHEN BOOL_OR(p.rubro_trabajador = 'N') THEN MAX(p.rem_asegurable)
                    ELSE SUM(p.rem_asegurable)
                END AS rem_asegurable_final,
                STRING_AGG(DISTINCT p.archivo_nombre, ', ') AS archivos
            FROM planilla_excel_afp p
            LEFT JOIN LATERAL (
                SELECT 
                    sub.relacion_laboral,
                    sub.inicio_relacion_laboral,
                    sub.cese_relacion_laboral,
                    sub.excepcion_de_aportar
                FROM planilla_excel_afp sub
                WHERE 
                    sub.n_secuencia = p.n_secuencia AND
                    sub.cuspp = p.cuspp AND
                    sub.tipo_documento = p.tipo_documento AND
                    sub.n_documento = p.n_documento
                ORDER BY sub.created_at DESC
                LIMIT 1
            ) AS ult ON TRUE
            GROUP BY $groupByCols, 
                     ult.relacion_laboral, 
                     ult.inicio_relacion_laboral, 
                     ult.cese_relacion_laboral, 
                     ult.excepcion_de_aportar
            ORDER BY p.apellido_paterno, p.apellido_materno, p.nombres
        ";

            $rows = DB::select($sql);

            return ApiResponse::success($rows, 'Lista combinada OK');
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }



    public function descargar_excel(Request $request)
    {
        try {
            // === QUERY ORIGINAL ===
            $groupCols = [
                'n_secuencia',
                'cuspp',
                'tipo_documento',
                'n_documento',
                'apellido_paterno',
                'apellido_materno',
                'nombres',
                'aporte_con_fin',
                'aporte_sin_fin',
                'aporte_empleador',
                'rubro_trabajador',
                'afp'
            ];
            $selectCols = array_map(fn($c) => "p.$c", $groupCols);
            $groupByCols = implode(', ', $selectCols);

            $sql = "
            SELECT 
                $groupByCols,
                ult.relacion_laboral,
                ult.inicio_relacion_laboral,
                ult.cese_relacion_laboral,
                ult.excepcion_de_aportar,
                CASE 
                    WHEN BOOL_OR(p.rubro_trabajador = 'N') THEN MAX(p.rem_asegurable)
                    ELSE SUM(p.rem_asegurable)
                END AS rem_asegurable_final,
                STRING_AGG(DISTINCT p.archivo_nombre, ', ') AS archivos
            FROM planilla_excel_afp p
            LEFT JOIN LATERAL (
                SELECT 
                    sub.relacion_laboral,
                    sub.inicio_relacion_laboral,
                    sub.cese_relacion_laboral,
                    sub.excepcion_de_aportar
                FROM planilla_excel_afp sub
                WHERE 
                    sub.n_secuencia = p.n_secuencia AND
                    sub.cuspp = p.cuspp AND
                    sub.tipo_documento = p.tipo_documento AND
                    sub.n_documento = p.n_documento
                ORDER BY sub.created_at DESC
                LIMIT 1
            ) AS ult ON TRUE
            GROUP BY $groupByCols, 
                     ult.relacion_laboral, 
                     ult.inicio_relacion_laboral, 
                     ult.cese_relacion_laboral, 
                     ult.excepcion_de_aportar
            ORDER BY p.apellido_paterno, p.apellido_materno, p.nombres
        ";

            $rows = DB::select($sql);
            if (empty($rows)) {
                return ApiResponse::validation([], 'No hay datos para exportar');
            }

            // === CREAR EXCEL ===
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Orden de columnas y nombres mostrados
            $headers = [
                'n_secuencia'             => 'N° Secuencia',
                'cuspp'                   => 'CUSPP',
                'tipo_documento'          => 'Tipo Documento',
                'n_documento'             => 'N° Documento',
                'apellido_paterno'        => 'Apellido Paterno',
                'apellido_materno'        => 'Apellido Materno',
                'nombres'                 => 'Nombres',
                'relacion_laboral'        => 'Relación Laboral',
                'inicio_relacion_laboral' => 'Inicio Relación Laboral',
                'cese_relacion_laboral'   => 'Cese Relación Laboral',
                'excepcion_de_aportar'    => 'Excepción de aportar',
                'rem_asegurable'          => 'Rem. asegurable',
                'aporte_con_fin'          => 'Aporte con fin',
                'aporte_sin_fin'          => 'Aporte sin fin',
                'aporte_empleador'        => 'Aporte empleador',
                'rubro_trabajador'        => 'Rubro trabajador',
                'afp'                     => 'AFP',
                'archivos'                => 'Archivos origen'
            ];

            // === Encabezados ===
            $col = 1;
            foreach ($headers as $key => $label) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $sheet->setCellValue($colLetter . '1', $label);
                $col++;
            }

            // === Llenar filas ===
            $rowNum = 2;
            foreach ($rows as $r) {
                $col = 1;
                foreach ($headers as $key => $label) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $valor = $r->$key ?? null;
                    if ($key === 'rem_asegurable') {
                        $valor = $r->rem_asegurable_final ?? null;
                    }
                    $sheet->setCellValue($colLetter . $rowNum, $valor);
                    $col++;
                }
                $rowNum++;
            }

            // === Formato de cabecera ===
            $lastCol = Coordinate::stringFromColumnIndex(count($headers));
            $headerRange = "A1:{$lastCol}1";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD'],
                ],
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                ],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(22);

            // === Bordes para todo el rango ===
            $dataRange = "A1:{$lastCol}" . ($rowNum - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']],
                ],
            ]);

            // Auto size
            foreach (range('A', $sheet->getHighestColumn()) as $colLetter) {
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // === Nombre de archivo ===
            $fileName = 'planilla_afp_combinada_' . date('Ymd_His') . '.xlsx';

            // === STREAM DESCARGA ===
            return new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }
}
