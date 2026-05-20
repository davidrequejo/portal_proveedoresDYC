<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlantillaEvaluacionController extends Controller
{
    
    public function index()
    {
       return view('plantilla_evaluacion');
    }
}
