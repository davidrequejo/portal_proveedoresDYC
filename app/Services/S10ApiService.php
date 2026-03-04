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

    public function buscarPorDocumento(string $documento): ?array
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
    }

    public function crearProveedor(array $data): array
    {
        $path = '/proveedor/crear';

        $response = $this->client()->post($path, $data);
        $this->logIfFailed($response, 'POST', $path, $data);

        $response->throw();

        return $response->json();
    }

    public function actualizarProveedor(string $codigo, array $data): array
    {
        $path = "/proveedor/{$codigo}/actualizar";

        $response = $this->client()->put($path, $data);
        $this->logIfFailed($response, 'PUT', $path, $data);

        $response->throw();

        return $response->json();
    }

    // Aquí puedes agregar más métodos para otras entidades (cuentas bancarias, contactos, etc.) siguiendo el mismo patrón


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

            return $response->json();
        } catch (RequestException $e) {
            if ($e->response && $e->response->status() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Crear una nueva cuenta bancaria en S10.
     *
     * @param array $data Datos según la estructura de IdentificadorCuentaBanco
     * @return int|string El ID de la cuenta creada (NroIdentificadorCuentaBanco)
     */
    public function crearCuentaBancaria(array $data)
    {
        $path = '/cuenta-banco/crear';

        $response = $this->client()->post($path, $data);
        $this->logIfFailed($response, 'POST', $path, $data);

        $response->throw();

        // Suponiendo que S10 devuelve el ID en el campo 'id' o 'NroIdentificadorCuentaBanco'
        return $response->json('id') ?? $response->json('NroIdentificadorCuentaBanco');
    }

    /**
     * Actualizar una cuenta bancaria existente en S10.
     *
     * @param int|string $idCuenta NroIdentificadorCuentaBanco en S10
     * @param array $data Datos actualizados
     * @return array Respuesta de S10
     */
    public function actualizarCuentaBancaria($idCuenta, array $data): array
    {
        $path = "/cuenta-banco/{$idCuenta}/actualizar";

        $response = $this->client()->put($path, $data);
        $this->logIfFailed($response, 'PUT', $path, $data);

        $response->throw();

        return $response->json();
    }

    /**
     * Eliminar una cuenta bancaria en S10.
     *
     * @param int|string $idCuenta NroIdentificadorCuentaBanco en S10
     * @return array Respuesta de S10
     */
    public function eliminarCuentaBancaria($idCuenta): array
    {
        $path = "/cuenta-banco/{$idCuenta}/eliminar";

        $response = $this->client()->delete($path);
        $this->logIfFailed($response, 'DELETE', $path);

        $response->throw();

        return $response->json();
    }


   
}