<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiconsultaReniecSunat;

class ApiReniecSunatController extends Controller
{
    /**
     * Buscar DNI en RENIEC
     */
    public function buscarReniec(Request $request)
    {
        // Validación del parámetro DNI
        $validated = $request->validate([
            'dni' => 'required|numeric|digits:8',
        ]);

        $dni = $request->dni;

        // Llamar al helper para obtener la información del DNI
        $data = \App\Helpers\ApiconsultaReniecSunat::datosReniec($dni);

        // Retornar respuesta JSON con los datos obtenidos
        return response()->json($data);
    }

    /**
     * Buscar RUC en SUNAT
     */
    public function buscarSunat(Request $request)
    {
        // Validación del parámetro RUC
        $validated = $request->validate([
            'ruc' => 'required|numeric|digits:11',
        ]);

        $ruc = $request->ruc;

        // Llamar al helper para obtener la información del RUC
        $data = \App\Helpers\ApiconsultaReniecSunat::datosSunat($ruc);

        // Retornar respuesta JSON con los datos obtenidos
        return response()->json($data);
    }
}
