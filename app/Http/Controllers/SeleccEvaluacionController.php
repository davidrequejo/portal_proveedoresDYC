<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UbigeoDistrito;
use App\Helpers\ApiResponse;

class SeleccEvaluacionController extends Controller
{
    // Método para obtener todos los distritos con provincia y departamento
    public function index()
    {
      try {
        return view('selecc_evaluacion');
      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }
}
