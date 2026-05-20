<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UbigeoDistrito;
use App\Helpers\ApiResponse;
use App\Models\PersonaFechaHomologacion;

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

    public function select2PersonaselecEvaluacion()
    {

        try {
        $data  = PersonaFechaHomologacion::seleccion_evaluacion();

        //var_dump($data); die(); // Verificar el contenido de $data

        $options = '<option value="" >Seleccionar</option>';
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idpersona.'" >' . e($t->nombre_razonsocial).'</option>';
        }

        return ApiResponse::success($options, 'lista de proveedores para selección evaluación');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }
    }
    



}
