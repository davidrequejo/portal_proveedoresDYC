<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Recurso;
use App\Models\TipoElemento;
use Illuminate\Http\Request;

class RecursoController extends Controller
{

    /** * Display a listing of the resource. */ 
    public function index(Request $req) { 
      $q = $req->q; $rows = Recurso::when($q, fn($w)=>$w->where('codigo','like',"%$q%") 
      ->orWhere('descripcion','like',"%$q%")->orWhere('cliente','like',"%$q%")) 
      ->orderByDesc('idrecurso')->paginate(10)->withQueryString(); 
      // $tipoelementos = TipoElemento::orderBy('descripcion')->get(); return view('recursos', compact('rows','q')); 
      return view('recursos', compact('rows','q'));
    }

    public function getlistar_recursos_x_nivel(Request $req)
    {
      try {
            $q = $req->q;

            $rows = Recurso::with('tipoElemento') ->when($q, function ($w) use ($q) {
                $w->where(function($qq) use ($q){
                    $qq->where('codigo', 'like', "%{$q}%") ->orWhere('descripcion', 'like', "%{$q}%") ->orWhere('nivel', 'like', "%{$q}%"); // opcional
                });
            })->where('nivel', '<', 5)   // 👈 aquí filtras niveles menores a 5
            ->orderBy('idrecurso')->paginate(20)->withQueryString();

            return ApiResponse::success($rows, 'Lista de recursos obtenida');

        } catch (\Throwable $e) {
					return ApiResponse::error($e);
			  }
    }

    // RecursoController
    public function getlistar_recursos_ultimo_nivel(Request $r)
    {
        try {

            // Inputs dinámicos
            $perPage = (int) $r->input('per_page', 10);      // tamaño de página
            $page    = (int) $r->input('page', 1);           // página actual
            $sort    = $r->input('sort', 'idrecurso');          // columna a ordenar
            $dir     = strtolower($r->input('dir', 'asc'));  // asc|desc
            $nivel   = $r->input('nivel', 5);             // nivel
            $prefix  = $r->input('codigo', null);            // prefijo de código
            $q       = trim($r->input('q', ''));  
            
            // Validar columnas de ordenamiento
            $validSorts = ['idrecurso','codigo','descripcion','nivel','tipo_elemento_id'];
            if (!in_array($sort, $validSorts, true)) {
                $sort = 'codigo';
            }
            $dir = $dir === 'desc' ? 'desc' : 'asc';

            // Construir query
            $query = Recurso::with('tipoElemento')
                ->when($q !== '', function ($w) use ($q) {
                    $w->where(function($qq) use ($q){
                        $qq->where('codigo', 'like', "%{$q}%")
                          ->orWhere('descripcion', 'like', "%{$q}%");
                    });
                })
                ->when($nivel, fn($w) => $w->where('nivel', $nivel))
                ->when($prefix, fn($w) => $w->where('codigo', 'like', "{$prefix}%"))
                ->orderBy($sort, $dir);

            // Paginación
            $rows = $query->paginate($perPage, ['*'], 'page', $page);

            // Transformar colección antes de enviar
            $rows->getCollection()->transform(function($item){
                return [
                    'idrecurso'     => $item->idrecurso,
                    'codigo'        => $item->codigo,
                    'descripcion'   => $item->descripcion,
                    'nivel'         => $item->nivel,
                    'tipo_elemento' => $item->tipoElemento?->descripcion ?? null,
                    'created_at' => $item->created_at,
                ];
            });

            return response()->json([
                'data'         => $rows->items(),
                'current_page' => $rows->currentPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
                'last_page'    => $rows->lastPage(),
                'from'         => $rows->firstItem(),
                'to'           => $rows->lastItem(),
                'sort'         => $sort,
                'dir'          => $dir,
                'q'            => $q,
                'nivel'        => $nivel,
                'prefix'       => $prefix,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage(),
            ], 500);
        }
    }

    public function getselect2TipoElementos(){
      //return response()->json( TipoElemento::orderBy('descripcion')->get() );

      try {
        $data = TipoElemento::orderBy('descripcion')->get();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idtipoelemento.'" codigo="'.$t->codigo.'">' . e($t->codigo).' - '.e($t->descripcion) . '</option>';
        }

        return ApiResponse::success($options, 'Lista de tipos obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }


    }
}
