<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, string $message = 'OK')
    {
        return response()->json([
            'status'     => true,
            'code_error' => 0,
            'message'    => $message,
            'data'       => $data,
        ], 200);
    }

    public static function error(\Throwable $e, int $code = 500)
    {
        return response()->json([
            'status'     => false,
            'code_error' => $e->getCode(),
            'message'    => $e->getMessage(),
            'data'       => '<br><b>Rutas de errores:</b><br>' . nl2br($e->getTraceAsString()),
        ], $code);
    }

    public static function validation(array $errors, string $message = 'Errores de validación')
    {
        return response()->json([
            'status'     => false,
            'code_error' => 422,
            'message'    => $message,
            'data'       => $errors,
        ], 422);
    }
}
