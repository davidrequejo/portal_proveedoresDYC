<?php

namespace App\Traits;

use App\Models\Logbd;
use Illuminate\Support\Facades\Auth;

trait RegistraLogCompleto
{
    /**
     * Obtener la configuración según la tabla
     */
    abstract public function getConfigLog($tabla);
    
    /**
     * Registrar un snapshot completo
     */
    public function registrarSnapshot($modelo, $tabla, $id_registro, $accion = 'REGISTRO_INICIAL')
    {
        $config = $this->getConfigLog($tabla);
        $labels = $config['labels'] ?? [];
        $formatters = $config['formatters'] ?? [];
        
        $datos = $modelo->toArray();
        $datosFormateados = [];
        
        foreach ($labels as $campoBD => $nombreLegible) {
            if (isset($datos[$campoBD]) && $datos[$campoBD] !== null && $datos[$campoBD] !== '') {
                $valor = $datos[$campoBD];
                
                // Aplicar formateador si existe
                if (isset($formatters[$campoBD])) {
                    $valor = $this->ejecutarFormateador($formatters[$campoBD], $valor);
                }
                
                $datosFormateados[$nombreLegible] = $valor;
            }
        }
        
        return Logbd::create([
            'nombre_tabla'     => $tabla,
            'id_registrotabla' => $id_registro,
            'id_user'          => Auth::id() ?? 1,
            'idpersona'        => Auth()->user()->idpersona,
            'observacion'      => json_encode($datosFormateados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'accion_realizada' => $accion,
            'user_created'     => Auth::id() ?? 1,
            'user_updated'     => Auth::id() ?? 1,
            'estado_trash'     => '0',
            'estado_delete'    => '0',
        ]);
    }
    
    /**
     * Registrar solo cambios
     */
    public function registrarCambios($modelo, $tabla, $id_registro, $cambios, $accion = 'ACTUALIZAR')
    {
       // var_dump($cambios);die();
        $config = $this->getConfigLog($tabla);
        $labels = $config['labels'] ?? [];
        $formatters = $config['formatters'] ?? [];
        $ignorar = $config['ignorar'] ?? ['updated_at', 'user_updated', 'created_at', 'user_created'];
        
        $cambiosFormateados = [];
        
        foreach ($cambios as $campo => $valor) {
            if (!isset($labels[$campo])) continue;
            if (in_array($campo, $ignorar)) continue;
            
            $valorFormateado = $valor ?? '-';
            
            // Aplicar formateador si existe
            if (isset($formatters[$campo])) {
                $valorFormateado = $this->ejecutarFormateador($formatters[$campo], $valor,$modelo);
            }
            
            $cambiosFormateados[$labels[$campo]] = $valorFormateado;
        }
        
        if (empty($cambiosFormateados)) return null;
        
        return Logbd::create([
            'nombre_tabla'     => $tabla,
            'id_registrotabla' => $id_registro,
            'id_user'          => Auth::id() ?? 1,
            'idpersona'        => Auth()->user()->idpersona,
            'observacion'      => json_encode($cambiosFormateados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'accion_realizada' => $accion,
            'user_created'     => Auth::id() ?? 1,
            'user_updated'     => Auth::id() ?? 1,
            'estado_trash'     => '0',
            'estado_delete'    => '0',
        ]);
    }
    
    /**
     * Ejecutar formateador por nombre
     */
    public function ejecutarFormateador($formateador, $valor, $modelo = null)
    {
        if ($valor === null || $valor === '') return '-';
        
        switch ($formateador) {
            // 📌 PERSONA / PROVEEDOR  tipo_entidad_sunat
            case 'sexo': return $valor == 'M' ? 'Masculino' : ($valor == 'F' ? 'Femenino' : $valor);
            case 'fecha': return date('d/m/Y', strtotime($valor));
            //case 'celular': return preg_replace('/(\d{3})(\d{3})(\d{3})/', '$1 $2 $3', $valor);
           //case 'documento': return number_format($valor, 0, '', ' ');
            case 'email': return strtolower($valor);

            // persona_cuentabancaria
            case 'moneda': return $valor == '01' ? 'S/' : ($valor == '02' ? 'U$' : $valor);
            // 🔥 NUEVO: FORMATEADOR PARA BANCOS (usa relación)
            case 'banco_completo':
                if ($valor === null || $valor === '') return '-';
                
                
                $bancoData = [ 'id' => $valor,  'nombre' => 'No especificado', 'codigo_bank_s10' => null ];
                
                // Intentar obtener datos del banco
                if ($modelo && method_exists($modelo, 'banco') && $modelo->banco) {
                    
                    $bancoData['nombre'] = $modelo->banco->descripcion ?? 'No especificado';
                    $bancoData['codigo_bank_s10'] = $modelo->banco->codigo_bank_s10 ?? null;

                    
                } else {
                    // Fallback: consultar directamente
                    $banco = \DB::table('banco')->where('idbanco', $valor)->first();
                    if ($banco) {
                        $bancoData['nombre'] = $banco->descripcion ?? 'No especificado';
                        $bancoData['codigo_bank_s10'] = $banco->codigo_bank_s10 ?? null;
                    }
                   // var_dump($bancoData);die();
                }
                
                return $bancoData;

            case 'tipo_cuenta':
                $tipos = [
                    'C' => 'Corriente',
                    'A' => 'Ahorros',
                    'D' => 'Detracción',
                ];
                return $tipos[$valor] ?? $valor;

            // 📌 HOMOLOGACIÓN
            case 'estado_homologacion':
                return $this->formatearEstadoHomologacion($valor);
            case 'fecha_homologacion':
                return date('d/m/Y H:i', strtotime($valor));
            
            // 📌 DOCUMENTOS
            case 'tipo_documento':
                return $valor == '6' ? 'RUC' : ($valor == '1' ? 'DNI' : $valor);
            case 'tamano_archivo':
                return $this->formatearBytes($valor);
            
            // 📌 BANCOS
            case 'cuenta_bancaria':
                return preg_replace('/(\d{4})(\d{4})(\d{4})/', '$1-$2-$3', $valor);
            case 'monto':
                return 'S/ ' . number_format($valor, 2, '.', ',');
            
            default:
                return $valor;
        }
    }
    
    /**
     * Formateadores específicos (puedes sobrescribirlos)
     */
    public function formatearEstadoHomologacion($valor)
    {
        $estados = [
            '0' => 'Pendiente',
            '1' => 'Vigente',
            '2' => 'Vencido',
            '3' => 'No Iniciada',
            'verificacion' => 'Verificación',
            'enviado' => 'Enviado'
        ];
        return $estados[$valor] ?? $valor;
    }
    
    public function formatearTipoDocumento($valor)
    {
        $tipos = [
            'DNI' => 'Documento Nacional',
            'RUC' => 'Registro Único',
            'PAS' => 'Pasaporte',
            'CEX' => 'Carnet Extranjería'
        ];
        return $tipos[$valor] ?? $valor;
    }
    
    public function formatearBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes > 1024) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}