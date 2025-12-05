<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class ApiconsultaReniecSunat
{
    protected static $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Imp1bmlvcmNlcmNhZG9AdXBldS5lZHUucGUifQ.bzpY1fZ7YvpHU5T83b9PoDxHPaoDYxPuuqMqvCwYqsM";

    public static function datosReniec($dni)
    {
        $url = "https://dniruc.apisperu.com/api/v1/dni/{$dni}?token=" . self::$token;

        $response = Http::withoutVerifying()
            ->acceptJson()
            ->get($url);

        return $response->json();
    }

    public static function datosSunat($ruc)
    {
        $url = "https://dniruc.apisperu.com/api/v1/ruc/{$ruc}?token=" . self::$token;

        $response = Http::withoutVerifying()
            ->acceptJson()
            ->get($url);

        return $response->json();
    }
}