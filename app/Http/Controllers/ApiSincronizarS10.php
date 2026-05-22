<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\UbigeoDistrito;
use App\Models\PersonaCuentaBancaria;
use App\Models\Banco;
use App\Models\Logbd;
use App\Helpers\ApiResponse;
use App\Services\S10ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiSincronizarS10 extends Controller
{
    protected S10ApiService $s10Api;

    public function __construct(S10ApiService $s10Api)
    {
        $this->s10Api = $s10Api;
    }

    /**====================================================================== 
     =======----------S I N C R O N I Z A C I Ó N--------------=============== 
    ============---------P R O V E E D O R E S------------==================== 
    ====================================================================== */

        /**
     * Sincroniza un proveedor con S10.
     * Si al actualizar se obtiene 404, se resetea el código y se crea uno nuevo.
     */
    public function sincronizar(Request $request, Proveedor $proveedor, $idlogbd = null): JsonResponse|RedirectResponse
    {
        try {
            $documento = $proveedor->numero_documento;

            // --- INTENTO DE ACTUALIZACIÓN SI TIENE CÓDIGO ---
            if (!empty($proveedor->codigo_s10)) {
                try {
                    $datos = $this->mapearParaActualizacion($proveedor);
                    $respuesta = $this->s10Api->actualizarProveedorcliente($proveedor->codigo_s10, $datos);
                    // Si llegamos aquí, la actualización fue exitosa y tenemos respuesta
                } catch (RequestException $e) {
                    if ($e->response && $e->response->status() === 404) {
                        // El código guardado no existe en S10 → lo reseteamos y pasamos a crear
                        $proveedor->codigo_s10 = null;
                        $proveedor->save(); // Guardamos el reseteo en la BD local
                        // No definimos $respuesta aquí, dejamos que el flujo continúe a la creación
                    } else {
                        // Otro error (500, timeout, etc.) lo relanzamos
                        throw $e;
                    }
                }
            }

            // --- SI NO TIENE CÓDIGO (O SE RESETEÓ), BUSCAMOS O CREAMOS ---
            if (empty($proveedor->codigo_s10)) {
                $existente = $this->s10Api->buscarPorDocumento($documento);
                if ($existente && isset($existente['CodIdentificador'])) {
                    // Ya existe en S10 → actualizar con ese código
                    $proveedor->codigo_s10 = $existente['CodIdentificador'];
                    $proveedor->save();
                    $datos = $this->mapearParaActualizacion($proveedor);
                    $respuesta = $this->s10Api->actualizarProveedorcliente($proveedor->codigo_s10, $datos);
                } else {
                    // No existe → obtener nuevo código y crear
                    $nuevoCodigo = $this->s10Api->obtenerProximoCodigo();
                    if (!$nuevoCodigo) {
                        throw new \Exception('No se pudo obtener un código disponible de S10.');
                    }
                    $proveedor->codigo_s10 = $nuevoCodigo;
                    $proveedor->save();
                    $datos = $this->mapearParaCreacion($proveedor);
                    $respuesta = $this->s10Api->crearProveedorcliente($datos);
                }
            }

            // --- PROCESAR RESPUESTA DE LA API .NET ---
            if ($respuesta['ok']) {
                $proveedor->estado_sincronizacions10 = '1';
                $proveedor->save();
                $mensaje = 'Proveedor sincronizado correctamente con S10.';
                $data = ['codigo_s10' => $proveedor->codigo_s10];
                $tipo = 'success';

                // Actualizar el campo estado_sincronizacions10 en la tabla logbd
                Logbd::where('nombre_tabla', 'persona')
                    ->where('id_registrotabla', $proveedor->idpersona)
                    ->update([
                        'estado_sincronizacions10' => 1
                    ]);


            } else {
                $mensaje = 'Error al sincronizar el proveedor.';
                $data = [];
                $tipo = 'error';
            }

            if ($request->wantsJson()) {
                if ($tipo === 'success') {
                    return ApiResponse::success($data, $mensaje);
                } else {
                    return ApiResponse::error(new \Exception($mensaje), 400);
                }
            }

            return back()->with($tipo, $mensaje);

        } catch (RequestException $e) {
            report($e);
            $errorMsg = 'Error de comunicación con S10.';
            if ($e->response) {
                $errorMsg .= ' Código: ' . $e->response->status();
            }
            if ($request->wantsJson()) {
                return ApiResponse::error($e, 500);
            }
            return back()->with('error', $errorMsg);
        } catch (\Exception $e) {
            report($e);
            $errorMsg = 'Error interno: ' . $e->getMessage();
            if ($request->wantsJson()) {
                return ApiResponse::error($e, 500);
            }
            return back()->with('error', $errorMsg);
        }
    }


    /**
     * Mapeo para creación: se envían todos los campos (incluyendo null)
     */
    private function mapearParaCreacion(Proveedor $proveedor): array
    {
        return [
            'CodIdentificador' => $proveedor->codigo_s10,
            'Descripcion' => $proveedor->nombre_razonsocial,
            'Abreviatura' => null,
            'RUC' => $proveedor->numero_documento,
            'Rubro' => null,
            'Direccion' => $proveedor->direccion,
            'CodLugar' => UbigeoDistrito::getCodigoReniecById($proveedor->distrito),
            'CodPostal' => null,
            'DireccionPostal' => null,
            'CodLugarPostal' => null,
            'CodPostalPostal' => null,
            'Telefono1' => $proveedor->celular,
            'Telefono2' => null,
            'Telefono3' => null,
            'Fax' => null,
            'TelefonoMovil' => $proveedor->celular,
            'Email' => $proveedor->email,
            'Internet' => null,
            'Aniversario' => !empty($proveedor->fecha_nacimiento) ? \Carbon\Carbon::parse($proveedor->fecha_nacimiento)->format('Y-m-d') : null,
            'RucAntiguo' => null,
            'NaturalJuridica' => ($proveedor->tipo_entidad_sunat == 'JURIDICA') ? true : false,
            'CodTratamiento' => $proveedor->tratamiento_pers_natural,
            'ApellidoPaterno' => $proveedor->apellido_paterno_per_natural,
            'ApellidoMaterno' => $proveedor->apellido_materno_per_natural,
            'Nombres' => $proveedor->nombre_persona_natural,
            'Sexo' => $proveedor->sexo,
            'DNI' => $proveedor->ruc_persona_natural,
            'CodigoAlterno' => null,
            'CodLogo' => null,
            'CodFormaDePago' => null,
            'Customizacion' => null,
            'NroRubroIdentificador' => null,
            'ESSALUD' => null,
            'AFP' => null,
            'Nextel' => null,
            'RPM' => null,
            'RPC' => null,
            'Anexo' => null,
            'CodMonedaProveedor' => null,
            'Retencion' => false,
            'RetencionP' => 0,
            'Detraccion' => false,
            'DetraccionP' => 0,
            'Percepcion' => false,
            'PercepcionP' => 0,
            'DescripcionAlterna' => null,
            'MedioDePago' => 0,
            'DireccionCobranza' => 0,
            'CodPaisOrigen' => 'PE',
            'NroIdentificadorCategoria' => null,
            'Skype' => null,
            'MSN' => null,
            'NumeroAutorizacionProveedor' => null,
            'FechaCaducidadAutorizacion' => null,
            'NumeroCuspp' => null,
            'NroTipoDoc' => null,
            'NroNacionalidad' => null,
            'NroRegimenLaboral' => null,
            'NroTipoTrabajador' => null,
            'NroNivelEducativo' => null,
            'NroEstadoDomicilio' => null,
            'Clave' => null,
            'BuenContribuyente' => false,
            'NroAgrupaIdentificador' => null,
            'Emaildesc' => null,
            'NroEstadoCivil' => null,
            'GrupoSanguineo' => null,
            'GranComprador' => false,
            'Latitud' => null,
            'Longitud' => null,
            'CodEstablecimiento' => null,
            'Auxiliar1' => null,
            'Auxiliar2' => null,
            'Activo' => $proveedor->estado == '1',
            'CodTipoIdentificador' => '02',   // 02 = Proveedor
        ];
    }

    /**
     * Mapeo para actualización: solo se incluyen campos con valor (no null)
     */
    private function mapearParaActualizacion(Proveedor $proveedor): array
    {
        $data = [];

        // Solo agregamos si el valor no es null (excepto booleanos false que sí se incluyen)
        if (!is_null($proveedor->nombre_razonsocial)) $data['Descripcion'] = $proveedor->nombre_razonsocial;
        if (!is_null($proveedor->numero_documento)) $data['RUC'] = $proveedor->numero_documento;
        if (!is_null($proveedor->direccion)) $data['Direccion'] = $proveedor->direccion;
        if (!is_null(UbigeoDistrito::getCodigoReniecById($proveedor->distrito))) $data['CodLugar'] = UbigeoDistrito::getCodigoReniecById($proveedor->distrito);
        if (!is_null($proveedor->celular)) {
            $data['Telefono1'] = $proveedor->celular;
            $data['TelefonoMovil'] = $proveedor->celular;
        }
        if (!is_null($proveedor->email)) $data['Email'] = $proveedor->email;
        if (!is_null($proveedor->fecha_nacimiento)) $data['Aniversario'] =  $proveedor->fecha_nacimiento ? \Carbon\Carbon::parse($proveedor->fecha_nacimiento)->format('Y-m-d') : null;
        if (!is_null($proveedor->tratamiento_pers_natural)) $data['CodTratamiento'] = $proveedor->tratamiento_pers_natural;
        if (!is_null($proveedor->apellido_paterno_per_natural)) $data['ApellidoPaterno'] = $proveedor->apellido_paterno_per_natural;
        if (!is_null($proveedor->apellido_materno_per_natural)) $data['ApellidoMaterno'] = $proveedor->apellido_materno_per_natural;
        if (!is_null($proveedor->nombre_persona_natural)) $data['Nombres'] = $proveedor->nombre_persona_natural;
        if (!is_null($proveedor->sexo)) $data['Sexo'] = $proveedor->sexo;
        if (!is_null($proveedor->ruc_persona_natural)) $data['DNI'] = $proveedor->ruc_persona_natural;

        // NaturalJuridica siempre se envía porque es booleano (puede ser false)
        $data['NaturalJuridica'] = ($proveedor->tipo_entidad_sunat == 'JURIDICA') ? true : false;

        // Activo se envía siempre
        $data['Activo'] = $proveedor->estado == '1';

            // 👇 Agrega esta línea
        $data['CodTipoIdentificador'] = '02';

        return $data;
    }

    /**====================================================================== 
     =======----------S I N C R O N I Z A C I Ó N--------------=============== 
    ============---------C L I E N T E S ------------==================== 
    ====================================================================== */
    public function sincronizar_cliente(Request $request, Proveedor $proveedor, $idlogbd = null): JsonResponse|RedirectResponse
    {
        try {
            $documento = $proveedor->numero_documento;
           // var_dump($documento); die(); // Debug: Ver número de documento antes de sincronizar

            // --- INTENTO DE ACTUALIZACIÓN SI TIENE CÓDIGO ---
            if (!empty($proveedor->codigo_s10)) {
                try {
                    $datos = $this->mapearParaActualizacioncliente($proveedor);
                    $respuesta = $this->s10Api->actualizarProveedorcliente($proveedor->codigo_s10, $datos);
                    // Si llegamos aquí, la actualización fue exitosa y tenemos respuesta
                } catch (RequestException $e) {
                    if ($e->response && $e->response->status() === 404) {
                        // El código guardado no existe en S10 → lo reseteamos y pasamos a crear
                        $proveedor->codigo_s10 = null;
                        $proveedor->save(); // Guardamos el reseteo en la BD local
                        // No definimos $respuesta aquí, dejamos que el flujo continúe a la creación
                    } else {
                        // Otro error (500, timeout, etc.) lo relanzamos
                        throw $e;
                    }
                }
            }

            // --- SI NO TIENE CÓDIGO (O SE RESETEÓ), BUSCAMOS O CREAMOS ---
            if (empty($proveedor->codigo_s10)) {
                $existente = $this->s10Api->buscarPorDocumento($documento);
                if ($existente && isset($existente['CodIdentificador'])) {
                    // Ya existe en S10 → actualizar con ese código
                    $proveedor->codigo_s10 = $existente['CodIdentificador'];
                    $proveedor->save();
                    $datos = $this->mapearParaActualizacioncliente($proveedor);
                    $respuesta = $this->s10Api->actualizarProveedorcliente($proveedor->codigo_s10, $datos);
                } else {
                    // No existe → obtener nuevo código y crear
                    $nuevoCodigo = $this->s10Api->obtenerProximoCodigo();
                    if (!$nuevoCodigo) {
                        throw new \Exception('No se pudo obtener un código disponible de S10.');
                    }
                    $proveedor->codigo_s10 = $nuevoCodigo;
                    $proveedor->save();
                    $datos = $this->mapearParaCreacioncliente($proveedor);
                    //var_dump($datos); die(); // Debug: Ver datos mapeados para creación antes de llamar a la API
                    $respuesta = $this->s10Api->crearProveedorcliente($datos);
                }
            }

            // --- PROCESAR RESPUESTA DE LA API .NET ---
            if ($respuesta['ok']) {
                $proveedor->estado_sincronizacions10 = '1';
                $proveedor->save();
                $mensaje = 'Proveedor sincronizado correctamente con S10.';
                $data = ['codigo_s10' => $proveedor->codigo_s10];
                $tipo = 'success';

                // Actualizar el campo estado_sincronizacions10 en la tabla logbd
                Logbd::where('nombre_tabla', 'persona')
                    ->where('id_registrotabla', $proveedor->idpersona)
                    ->update([
                        'estado_sincronizacions10' => 1
                    ]);


            } else {
                $mensaje = 'Error al sincronizar el proveedor.';
                $data = [];
                $tipo = 'error';
            }

            if ($request->wantsJson()) {
                if ($tipo === 'success') {
                    return ApiResponse::success($data, $mensaje);
                } else {
                    return ApiResponse::error(new \Exception($mensaje), 400);
                }
            }

            return back()->with($tipo, $mensaje);

        } catch (RequestException $e) {
            report($e);
            $errorMsg = 'Error de comunicación con S10.';
            if ($e->response) {
                $errorMsg .= ' Código: ' . $e->response->status();
            }
            if ($request->wantsJson()) {
                return ApiResponse::error($e, 500);
            }
            return back()->with('error', $errorMsg);
        } catch (\Exception $e) {
            report($e);
            $errorMsg = 'Error interno: ' . $e->getMessage();
            if ($request->wantsJson()) {
                return ApiResponse::error($e, 500);
            }
            return back()->with('error', $errorMsg);
        }
    }

        /**
     * Mapeo para creación: se envían todos los campos (incluyendo null)
     */
    private function mapearParaCreacioncliente(Proveedor $proveedor): array
    {
        return [
            'CodIdentificador' => $proveedor->codigo_s10,
            'Descripcion' => $proveedor->nombre_razonsocial,
            'Abreviatura' => null,
            'RUC' => $proveedor->numero_documento,
            'Rubro' => null,
            'Direccion' => $proveedor->direccion,
            'CodLugar' => UbigeoDistrito::getCodigoReniecById($proveedor->distrito),
            'CodPostal' => null,
            'DireccionPostal' => null,
            'CodLugarPostal' => null,
            'CodPostalPostal' => null,
            'Telefono1' => $proveedor->celular,
            'Telefono2' => null,
            'Telefono3' => null,
            'Fax' => null,
            'TelefonoMovil' => $proveedor->celular,
            'Email' => $proveedor->email,
            'Internet' => null,
            'Aniversario' => !empty($proveedor->fecha_nacimiento)? \Carbon\Carbon::parse($proveedor->fecha_nacimiento)->format('Y-m-d') : null,
            'RucAntiguo' => null,
            'NaturalJuridica' => ($proveedor->tipo_entidad_sunat == 'JURIDICA') ? true : false,
            'CodTratamiento' => $proveedor->tratamiento_pers_natural,
            'ApellidoPaterno' => $proveedor->apellido_paterno_per_natural,
            'ApellidoMaterno' => $proveedor->apellido_materno_per_natural,
            'Nombres' => $proveedor->nombre_persona_natural,
            'Sexo' => $proveedor->sexo,
            'DNI' => $proveedor->ruc_persona_natural,
            'CodigoAlterno' => null,
            'CodLogo' => null,
            'CodFormaDePago' => null,
            'Customizacion' => null,
            'NroRubroIdentificador' => null,
            'ESSALUD' => null,
            'AFP' => null,
            'Nextel' => null,
            'RPM' => null,
            'RPC' => null,
            'Anexo' => null,
            'CodMonedaProveedor' => null,
            'Retencion' => false,
            'RetencionP' => 0,
            'Detraccion' => false,
            'DetraccionP' => 0,
            'Percepcion' => false,
            'PercepcionP' => 0,
            'DescripcionAlterna' => null,
            'MedioDePago' => 0,
            'DireccionCobranza' => 0,
            'CodPaisOrigen' => 'PE',
            'NroIdentificadorCategoria' => null,
            'Skype' => null,
            'MSN' => null,
            'NumeroAutorizacionProveedor' => null,
            'FechaCaducidadAutorizacion' => null,
            'NumeroCuspp' => null,
            'NroTipoDoc' => null,
            'NroNacionalidad' => null,
            'NroRegimenLaboral' => null,
            'NroTipoTrabajador' => null,
            'NroNivelEducativo' => null,
            'NroEstadoDomicilio' => null,
            'Clave' => null,
            'BuenContribuyente' => false,
            'NroAgrupaIdentificador' => null,
            'Emaildesc' => null,
            'NroEstadoCivil' => null,
            'GrupoSanguineo' => null,
            'GranComprador' => false,
            'Latitud' => null,
            'Longitud' => null,
            'CodEstablecimiento' => null,
            'Auxiliar1' => null,
            'Auxiliar2' => null,
            'Activo' => $proveedor->estado == '1',
            'CodTipoIdentificador' => '01',
        ];
    }

    /**
     * Mapeo para actualización: solo se incluyen campos con valor (no null)
     */
    private function mapearParaActualizacioncliente(Proveedor $proveedor): array
    {
        $data = [];

        // Solo agregamos si el valor no es null (excepto booleanos false que sí se incluyen)
        if (!is_null($proveedor->nombre_razonsocial)) $data['Descripcion'] = $proveedor->nombre_razonsocial;
        if (!is_null($proveedor->numero_documento)) $data['RUC'] = $proveedor->numero_documento;
        if (!is_null($proveedor->direccion)) $data['Direccion'] = $proveedor->direccion;
        if (!is_null(UbigeoDistrito::getCodigoReniecById($proveedor->distrito))) $data['CodLugar'] = UbigeoDistrito::getCodigoReniecById($proveedor->distrito);
        if (!is_null($proveedor->celular)) {
            $data['Telefono1'] = $proveedor->celular;
            $data['TelefonoMovil'] = $proveedor->celular;
        }
        if (!is_null($proveedor->email)) $data['Email'] = $proveedor->email;
        if (!is_null($proveedor->fecha_nacimiento)) $data['Aniversario'] =  $proveedor->fecha_nacimiento ? \Carbon\Carbon::parse($proveedor->fecha_nacimiento)->format('Y-m-d') : null;
        if (!is_null($proveedor->tratamiento_pers_natural)) $data['CodTratamiento'] = $proveedor->tratamiento_pers_natural;
        if (!is_null($proveedor->apellido_paterno_per_natural)) $data['ApellidoPaterno'] = $proveedor->apellido_paterno_per_natural;
        if (!is_null($proveedor->apellido_materno_per_natural)) $data['ApellidoMaterno'] = $proveedor->apellido_materno_per_natural;
        if (!is_null($proveedor->nombre_persona_natural)) $data['Nombres'] = $proveedor->nombre_persona_natural;
        if (!is_null($proveedor->sexo)) $data['Sexo'] = $proveedor->sexo;
        if (!is_null($proveedor->ruc_persona_natural)) $data['DNI'] = $proveedor->ruc_persona_natural;

        // NaturalJuridica siempre se envía porque es booleano (puede ser false)
        $data['NaturalJuridica'] = ($proveedor->tipo_entidad_sunat == 'JURIDICA') ? true : false;

        // Activo se envía siempre
        $data['Activo'] = $proveedor->estado == '1';

            // 👇 Agrega esta línea
        $data['CodTipoIdentificador'] = '01';

        return $data;
    }


    /**====================================================================== 
       ============----------SINCRONIZACIÓN--------------==================== 
       ============---------CUENTAS BANCARIAS------------==================== 
       ====================================================================== */
    
    public function sincronizarCuentasBancarias(Request $request, Proveedor $proveedor, S10ApiService $s10Api)
    {
        try {
            // 1. Validar que el proveedor ya tenga código en S10
            if (empty($proveedor->codigo_s10)) {
                $mensaje = 'El proveedor no está sincronizado con S10. Primero sincronice el proveedor.';
                return $this->handleResponse($request, $mensaje, 'error');
            }

            // 2. Obtener las cuentas bancarias activas del proveedor (no eliminadas)
            $cuentasLocales = PersonaCuentaBancaria::obtenerCuentasActivasProveedor($proveedor->idpersona);

            if ($cuentasLocales->isEmpty()) {
                $mensaje = 'El proveedor no tiene cuentas bancarias activas para sincronizar.';
                return $this->handleResponse($request, $mensaje, 'warning');
            }

            //var_dump($cuentasLocales->toArray()); die(); // Debug: Ver cuentas locales antes de sincronizar

            $creadas = 0;
            $existentes = 0;
            $errores = [];

            DB::beginTransaction();

            foreach ($cuentasLocales as $cuenta) {
                try {
                    // 3. Mapear los campos locales al formato que espera S10
                    $dataS10 = $this->mapCuentaToS10($cuenta, $proveedor);

                    //var_dump($cuenta->idbanco); die(); // Debug: Ver datos mapeados para S10 antes de buscar/crear
                    Log::info('Buscando cuenta con:', [
                        'codigo_s10' => $proveedor->codigo_s10,
                        'Banco_ID' => $dataS10['Banco_ID'],
                        'NoCuenta' => $dataS10['NoCuenta']
                    ]);
                    // 4. Buscar si ya existe en S10 (por CodIdentificador, Banco_ID, NoCuenta)
                    $existente = $s10Api->buscarCuentaBancaria(
                        $proveedor->codigo_s10,
                        $dataS10['Banco_ID'],
                        $dataS10['NoCuenta']
                    );

                    if ($existente) {
                        // Ya existe → obtenemos el ID del campo correcto
                        $idS10 = $existente['NoIdentificadorCuentaBanco'] 
                            ?? $existente['NroIdentificadorCuentaBanco'] 
                            ?? null;
                        if (empty($cuenta->NroIdentificadorCuentaBancos10) && $idS10) {
                            $cuenta->NroIdentificadorCuentaBancos10 = $idS10;
                            $cuenta->save();
                            DB::table('logbd')
                            ->where('idpersona', $proveedor->idpersona)
                            ->where('id_registrotabla', $cuenta->idpersona_cuentabancaria)
                            ->where('nombre_tabla', 'persona_cuentabancaria')
                            ->update(['estado_sincronizacions10' => 1]);
                        }
                            DB::table('logbd')
                            ->where('idpersona', $proveedor->idpersona)
                            ->where('id_registrotabla', $cuenta->idpersona_cuentabancaria)
                            ->where('nombre_tabla', 'persona_cuentabancaria')
                            ->update(['estado_sincronizacions10' => 1]);
                        $existentes++;
                    } else {
                        Log::info('Creando cuenta en S10', $dataS10);
                        $s10Id = $s10Api->crearCuentaBancaria($dataS10);
                        if ($s10Id) {
                            $cuenta->NroIdentificadorCuentaBancos10 = $s10Id;
                            $cuenta->save();

                            DB::table('logbd')
                            ->where('idpersona', $proveedor->idpersona)
                            ->where('id_registrotabla', $cuenta->idpersona_cuentabancaria)
                            ->where('nombre_tabla', 'persona_cuentabancaria')
                            ->update(['estado_sincronizacions10' => 1]);
                            
                            $creadas++;
                        } else {
                            $errores[] = "Cuenta ID {$cuenta->idpersona_cuentabancaria}: No se obtuvo ID de S10";
                        }
                    }

                } catch (\Exception $e) {
                    $errores[] = "Cuenta ID {$cuenta->idpersona_cuentabancaria}: " . $e->getMessage();
                    Log::error('Error sincronizando cuenta bancaria', [
                        'cuenta_id' => $cuenta->idpersona_cuentabancaria,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $mensaje = "Sincronización completada. Creadas: $creadas, ya existentes: $existentes.";
            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode('; ', $errores);
                $tipo = 'warning';
            } else {
                $tipo = 'success';
            }

            return $this->handleResponse($request, $mensaje, $tipo);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error general en sincronización de cuentas bancarias', [
                'proveedor_id' => $proveedor->idpersona,
                'error' => $e->getMessage()
            ]);
            return $this->handleResponse($request, 'Error interno: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Mapea una cuenta bancaria local a la estructura que espera S10 (tabla IdentificadorCuentaBanco).
     */
    private function mapCuentaToS10(PersonaCuentaBancaria $cuenta, Proveedor $proveedor): array
    {
        // IMPORTANTE: Debes tener una forma de convertir tu idbanco local al Banco_ID de S10
        // Puede ser una configuración, una tabla de equivalencias, o llamar a un método del servicio.
       //$bancoIdS10 = $this->obtenerBancoIdS10($cuenta->idbanco);

        return [
            'CodIdentificador'          => $proveedor->codigo_s10,
            'Banco_ID'                  => Banco::getCodigos10Idbn($cuenta->idbanco), // o el método que tengas para obtener el código de banco para S10
            'NoCuenta'                  => $cuenta->numero_cuenta,
            'TipoCuentaBanco'           => $cuenta->tipocuenta, // ej. 'Corriente', 'Ahorros'
            'Descripcion'               => Banco::getabreviaturabn($cuenta->idbanco).' '.$cuenta->numero_cuenta.' '. ($cuenta->moneda=='01' ? 'S/' : 'U$'), // Ejemplo: "BCP 12345678";
            'CodMoneda'                 => $cuenta->moneda, // ej. 'PEN', 'USD'
            'IBAN'                      => '',
            'BBAN'                      => '',
            'Activo'                    => true, // Porque ya filtramos activas
            'Predeterminado'            => $cuenta->predeterminado == '1' ? true : false    , // Convertir a booleano
            'CIB'                       => $cuenta->cuenta_interbancaria ?? '',
            'NoCuentaCargo'             => $cuenta->numero_cuenta,
            'CodIdentificadorEmpresa'   => null,
            // Los campos de auditoría los genera S10 (CreacionUsuario, CreacionFecha)
        ];
    }

    /**
     * Helper para responder según el tipo de petición (JSON o redirección).
     */
    private function handleResponse(Request $request, string $mensaje, string $tipo)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => $tipo === 'success',
                'message' => $mensaje,
            ], $tipo === 'error' ? 500 : 200);
        }

        return redirect()->back()->with($tipo, $mensaje);
    }

    private function actualizarLogSincronizacion($idPersona,$idbanco,$noCuenta)
    {
        var_dump($idPersona,$idbanco,$noCuenta); die(); 
        // Posibles claves donde puede estar el número de cuenta en el JSON
        $claves = ['Moneda', 'CC1', 'CC2', 'CC3'];
        
        foreach ($claves as $clave) {
            $actualizado = DB::table('logbd')
                ->where('id_registrotabla', $idPersona)
                ->where('nombre_tabla', 'persona_cuentabancaria')
                ->whereRaw('JSON_EXTRACT(observacion, "$.Banco.id") = ?', [$idbanco])
                ->update(['estado_sincronizacions10' => 1]);
            
            if ($actualizado) {
                break; // Salir si ya se actualizó
            }
        }
    }


}