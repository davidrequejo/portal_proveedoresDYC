<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class S10ApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.s10.base_url'), '/');
        $this->apiKey  = (string) config('services.s10.key');
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout(30)

            // OJO: esto manda Authorization: Bearer <apiKey>
            ->withToken($this->apiKey)

            // Si tu API realmente usa header X-API-KEY, usa esto en vez de withToken:
            // ->withHeaders(['X-API-KEY' => $this->apiKey])

            // Reintentos suaves ante 5xx / timeouts (puedes comentar si no quieres)
            ->retry(2, 300, function ($exception, $request) {
                // retry si es timeout/conexion o 5xx
                if ($exception instanceof RequestException) {
                    $status = optional($exception->response)->status();
                    return $status >= 500;
                }
                return true;
            });
    }

    private function logIfFailed(Response $response, string $method, string $path, array $payload = []): void
    {
        if (!$response->failed()) return;

        $body = $response->body();
        $json = null;

        try {
            $json = $response->json();
        } catch (\Throwable $e) {
            // no es json válido
        }

        Log::error('S10 API FAIL', [
            'method' => $method,
            'url' => $this->baseUrl . $path,
            'status' => $response->status(),
            'reason' => $response->reason(),
            'request_payload' => $payload,
            'response_headers' => $response->headers(),
            'response_body' => $body,
            'response_json' => $json, // si aplica
        ]);
    }

    public function obtenerProximoCodigo(): ?string
    {
        $path = '/proveedor/proximo-codigo';

        $response = $this->client()->get($path);
        $this->logIfFailed($response, 'GET', $path);

        $response->throw();

        return $response->json('proximoCodigo');
    }

    /*public function buscarPorDocumento(string $documento): ?array
    {
        try {
            $response = $this->client()->get('/proveedor/buscar', ['documento' => $documento]);
            
            // Si la API responde 404, significa que no existe (no es un error)
            if ($response->status() === 404) {
                return null;
            }
            
            // Para otros errores (500, etc.) lanzamos excepción
            $response->throw();
            
            return $response->json('data');
        } catch (RequestException $e) {
            // Si la excepción es por 404, la manejamos aquí también
            if ($e->response && $e->response->status() === 404) {
                return null;
            }
            // Si no, relanzamos
            throw $e;
        }
    }*/
    public function buscarPorDocumento($documento)
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get($this->baseUrl . '/proveedor/buscar', [
                    'documento' => $documento
                ]);

            if ($response->status() === 404) {
                return null;
            }

            if ($response->status() === 409) {
                $body = $response->json();

                throw new \Exception(
                    $body['message'] ?? 'Se encontraron registros duplicados en S10. Debe revisar antes de sincronizar.'
                );
            }

            $response->throw();

            $json = $response->json();

            return $json['data'] ?? null;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Crear un nuevo proveedor/cliente en S10.
     *
     * @param array $data Datos del proveedor/cliente
     * @return array
     */
    public function crearProveedorcliente(array $data): array
    {
        $path = '/proveedor/crear';

        $response = $this->client()->post($path, $data);
        $this->logIfFailed($response, 'POST', $path, $data);

        $response->throw();

        return $response->json();
    }
    /*public function crearProveedorcliente(array $data)
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl . '/proveedor/crear', $data);

        \Log::info('Respuesta crear proveedor S10', [
            'status' => $response->status(),
            'body' => $response->body(),
            'data_enviada' => $data,
        ]);

        $response->throw();

        return $response->json();
    }*/

    public function actualizarProveedorcliente(string $codigo, array $data): array
    {
        $path = "/proveedor/{$codigo}/actualizar";

        $response = $this->client()->put($path, $data);
        $this->logIfFailed($response, 'PUT', $path, $data);

        $response->throw();

        return $response->json();
    }

    // Aquí puedes agregar más métodos para otras entidades (cuentas bancarias, contactos, etc.) siguiendo el mismo patrón
    // ==================== CUENTAS BANCARIAS ====================
    // ==================== CUENTAS BANCARIAS ====================

    /**
     * Buscar una cuenta bancaria en S10 por proveedor, banco y número de cuenta.
     *
     * @param string $codIdentificador Código del proveedor en S10 (CodIdentificador)
     * @param mixed $bancoId ID del banco en S10 (Banco_ID)
     * @param string $noCuenta Número de cuenta (NoCuenta)
     * @return array|null
     */
public function buscarCuentaBancaria(string $codIdentificador, $bancoId, string $noCuenta): ?array
{
    try {
        $response = $this->client()->get('/cuenta-banco/buscar', [
            'CodIdentificador' => $codIdentificador,
            'Banco_ID'         => $bancoId,
            'NoCuenta'         => $noCuenta,
        ]);

        if ($response->status() === 404) {
            return null;
        }

        $response->throw();

        $json = $response->json();

        // Si la respuesta tiene un campo 'data', lo procesamos
        if (isset($json['data'])) {
            $data = $json['data'];
            // Si es un array indexado, tomamos el primer elemento
            if (is_array($data) && array_keys($data) === range(0, count($data) - 1)) {
                return !empty($data) ? $data[0] : null;
            }
            // Si es un array asociativo, lo retornamos directamente
            if (is_array($data)) {
                return $data;
            }
        }
        // Si no hay 'data', asumimos que la respuesta es el objeto mismo
        return $json;
    } catch (RequestException $e) {
        if ($e->response && $e->response->status() === 404) {
            return null;
        }
        $responseBody = $e->response ? $e->response->body() : null;
        Log::error('Error al buscar cuenta bancaria en S10', [
            'codIdentificador' => $codIdentificador,
            'bancoId'          => $bancoId,
            'noCuenta'         => $noCuenta,
            'status'           => $e->response?->status(),
            'body'             => $responseBody,
        ]);
        // Retornamos null para que el flujo intente crear la cuenta
        return null;
    }
}

public function crearCuentaBancaria(array $data): ?string
{
    $path = '/cuenta-banco/crear';

    try {
        $response = $this->client()->post($path, $data);
        $this->logIfFailed($response, 'POST', $path, $data);
        $response->throw();

        $json = $response->json();
        // Intentamos obtener el ID de diferentes formas posibles
        return $json['id'] 
            ?? $json['NoIdentificadorCuentaBanco'] 
            ?? $json['NroIdentificadorCuentaBanco'] 
            ?? null;
    } catch (RequestException $e) {
        $responseBody = $e->response ? $e->response->body() : null;
        Log::error('Error al crear cuenta en S10', [
            'data'   => $data,
            'status' => $e->response?->status(),
            'body'   => $responseBody,
        ]);
        throw $e; // Relanzamos para que el controlador lo capture
    }
}

    /**
     * Actualizar una cuenta bancaria existente en S10.
     *
     * @param string $idCuenta NroIdentificadorCuentaBanco en S10
     * @param array $data Datos actualizados
     * @return array Respuesta de S10
     */
    public function actualizarCuentaBancaria(string $idCuenta, array $data): array
    {
        $path = "/cuenta-banco/{$idCuenta}/actualizar";

        try {
            $response = $this->client()->put($path, $data);
            $this->logIfFailed($response, 'PUT', $path, $data);
            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            $responseBody = $e->response ? $e->response->body() : null;
            Log::error('Error al actualizar cuenta bancaria en S10', [
                'idCuenta' => $idCuenta,
                'data'     => $data,
                'status'   => $e->response?->status(),
                'body'     => $responseBody,
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar una cuenta bancaria en S10.
     *
     * @param string $idCuenta NroIdentificadorCuentaBanco en S10
     * @return array Respuesta de S10
     */
    public function eliminarCuentaBancaria(string $idCuenta): array
    {
        $path = "/cuenta-banco/{$idCuenta}/eliminar";

        try {
            $response = $this->client()->delete($path);
            $this->logIfFailed($response, 'DELETE', $path);
            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            $responseBody = $e->response ? $e->response->body() : null;
            Log::error('Error al eliminar cuenta bancaria en S10', [
                'idCuenta' => $idCuenta,
                'status'   => $e->response?->status(),
                'body'     => $responseBody,
            ]);
            throw $e;
        }
    }
}
    
   
