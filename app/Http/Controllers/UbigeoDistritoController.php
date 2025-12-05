<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UbigeoDistrito;
use App\Helpers\ApiResponse;

class UbigeoDistritoController extends Controller
{
    // Método para obtener todos los distritos con provincia y departamento
    public function obtenerDistritos()
    {
      try {
        $data  = UbigeoDistrito::obtenerDistritos();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idubigeo_distrito.'" data-distrito="'.$t->distrito.'" data-provincia="'.$t->provincia.'" data-departamento="'.$t->departamento.'">' . e($t->distrito). '</option>';
        }

        return ApiResponse::success($options, 'Lista de Distritos obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }
}
