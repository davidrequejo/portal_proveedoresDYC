<?php

namespace App\Http\Controllers;

use App\Models\ImportarHoraDetalleOberti;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ImportarHoraDetalleObertiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    public function actualizarCelda(Request $request, ImportarHoraDetalleOberti $detalle)
    {
        try {
            // 1) Separamos por tipo para validar/convertir correctamente
            $camposHoras = [
                'lunes_hn','lunes_he','martes_hn','martes_he','miercoles_hn','miercoles_he',
                'jueves_hn','jueves_he','viernes_hn','viernes_he','sabado_hn','sabado_he','domingo_hn','domingo_he',
            ];

            // 2) “Cabecera” o texto: agrega aquí lo que quieras permitir editar además de dni
            $camposTexto = [
                'dni', 'apellidos_nombres', 'cargo', 'observaciones',
                'proyecto', 'partida_control', 'concepto',
                'nro_hoja', 'fecha_ingreso', 'hijos'
            ];

            $permitidos = array_merge($camposHoras, $camposTexto);

            // Validación dinámica por tipo de campo
            $data = $request->validate([
                'field' => ['required','string', Rule::in($permitidos)],
                'value' => ['nullable'], // la regla exacta se aplica más abajo según el tipo
            ], [], [
                'field' => 'Campo',
                'value' => 'Valor',
            ]);

            $field = $data['field'];
            $value = $data['value'];

            if (in_array($field, $camposHoras, true)) {
                // Reglas específicas para horas
                $request->validate([
                    'value' => ['nullable', 'numeric', 'min:0', 'max:24'],
                ], [], ['value' => 'Valor']);

                // Null o vacío => 0; redondeo a 2 decimales
                if ($value === null || $value === '') {
                    $value = 0;
                }
                $value = round((float)$value, 2);
            } else {
                // Reglas específicas para texto
                if ($field === 'dni') {
                    // Mantener ceros a la izquierda y solo dígitos (evita convertir a int)
                    $value = is_null($value) ? '' : preg_replace('/\D/', '', (string)$value);
                    // Si quieres forzar 8 dígitos:
                    // if ($value !== '' && strlen($value) !== 8) { throw \Illuminate\Validation\ValidationException::withMessages(['value' => 'El DNI debe tener 8 dígitos.']); }
                } elseif ($field === 'fecha_ingreso') {
                    // Acepta fecha o vacío
                    $request->validate([
                        'value' => ['nullable','date'],
                    ], [], ['value' => 'Fecha de ingreso']);
                    $value = $value ? date('Y-m-d', strtotime($value)) : null;
                } elseif ($field === 'hijos') {
                    // numérico entero >=0
                    $request->validate([
                        'value' => ['nullable','integer','min:0'],
                    ], [], ['value' => 'Hijos']);
                    $value = $value === null || $value === '' ? null : (int)$value;
                } else {
                    // Texto general
                    $request->validate([
                        'value' => ['nullable','string','max:255'],
                    ], [], ['value' => 'Valor']);
                    $value = is_null($value) ? '' : trim((string)$value);
                }
            }

            // Guardar solo el campo solicitado
            $detalle->{$field} = $value;
            $detalle->save();

            return \App\Helpers\ApiResponse::success([
                'id'    => $detalle->idregistro_horas_detalle,
                'field' => $field,
                'value' => $value,
            ], 'Actualizado correctamente');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return \App\Helpers\ApiResponse::validation($ve->errors());
        } catch (\Throwable $e) {
            return \App\Helpers\ApiResponse::error($e, 500);
        }
    }
}
