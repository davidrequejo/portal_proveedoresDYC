<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\DB; 
use App\Models\Tipo_estandar;
use Illuminate\Support\Facades\Mail;
use App\Mail\CredencialesProveedorMail;

class ProveedorController extends Controller
{
    public function index(Request $req)
    {        
        return view('proveedor');
    }

    public function crear_proveedor(Request $r)
    {
       try {
          // Validar los datos del formulario
          $data = $r->validate([
              'idtipo_persona' => 'required|string',
              'idbanco' => 'required|string',
              'idtipoestandarproveedor' => 'required|string',
              'tipo_entidad_sunat' => 'required|string',
              'tipo_documento' => 'required|string',
              'numero_documento' => 'required|string',
              'nombre_razonsocial' => 'required|string|max:255',
              'celular' => 'required|string|max:15',
              'direccion' => 'required|string|max:255',
              'email' => 'required|email',
              'distrito' => 'required|string',
              'provincia' => 'required|string',
              'departamento' => 'required|string',
              'usuario_portal' => 'required|string',
              'clave_portal' => 'required|string',

          ]);
          // 1. Crear  proveedor
          $createProveedor = Proveedor::create($data);

          // 2. Crear usuario para el proveedor
          $user = User::create([
              'idpersona' =>  $createProveedor->idpersona,
              'name'      => 'PPROVEEDOR', 
              'email'     => $r->usuario_portal,
              'password'  => bcrypt($r->clave_portal)
          ]);

          // 3. Registrar permisos en tabla intermedia
          if ($user->id   ) {

                  DB::table('usuario_permiso')->insert([
                      'users_id' => $user->id,
                      'idpermiso' => '10'
                  ]);
              
          }

          // 4. Enviar credenciales al correo del proveedor
          try {
              Mail::to($createProveedor->email)->send(
                  new CredencialesProveedorMail(
                      nombre: $createProveedor->nombre_razonsocial,
                      usuario: $r->usuario_portal,
                      clave: $r->clave_portal
                  )
              );
          } catch (\Throwable $e) {
              // opcional: log
              \Log::error("Error enviando correo proveedor: " . $e->getMessage());
          }



          return ApiResponse::success([
                'idpersona' => $createProveedor->idpersona
            ], 'Proveedor creado correctamente');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function Listar_Proveedores(Request $r)
    {
        // Parámetros de entrada del request
        $perPage = (int) $r->input('per_page', 20);           // Número de elementos por página (por defecto 20)
        $page    = (int) $r->input('page', 1);                // Página actual (por defecto 1)
        $sort    = $r->input('sort', 'idpersona');            // Columna a ordenar (por defecto 'idpersona')
        $dir     = $r->input('dir', 'asc');                   // Dirección de orden ('asc' o 'desc')
        $q       = trim($r->input('q', ''));                  // Término de búsqueda global

        // Columnas válidas para ordenar
        $validSorts = [
            'idpersona', 'nombre_razonsocial', 'apellidos_nombrecomercial', 'tipo_documento',
            'numero_documento', 'celular', 'direccion', 'distrito', 'provincia', 'departamento', 'email','tipo_entidad_sunat','estado'
        ];

        // Si la columna para ordenar no es válida, usamos 'idpersona'
        if (!in_array($sort, $validSorts, true)) {
            $sort = 'idpersona';
        }

        // Asegurarse de que la dirección de orden sea 'asc' o 'desc'
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        // Crear la consulta base
        $query = DB::table('persona as p')
            ->join('tipo_persona as tp', 'p.idtipo_persona', '=', 'tp.idtipo_persona')
            ->join('sunat_c06_doc_identidad as doc', 'p.tipo_documento', '=', 'doc.code_sunat')
            ->join('tipoestandarproveedor as test', 'p.idtipoestandarproveedor', '=', 'test.idtipoestandarproveedor')
            ->select(
                'p.idpersona',
                'p.idtipoestandarproveedor',
                'tp.descripcion as tipoPersona',
                'p.tipo_documento',
                'p.nombre_razonsocial',
                'p.apellidos_nombrecomercial',
                'doc.abreviatura',
                'p.numero_documento',
                'p.celular',
                'p.direccion',
                'p.distrito',
                'p.provincia',
                'p.departamento',
                'p.email',
                'p.tipo_entidad_sunat',
                'test.descripcion as tipo_estandar',
                'test.nroDocumentos',
                'p.estado'
            )
            ->where('p.estado', '1')  // Filtrar proveedores activos
            ->where('p.estado_delete', '1');  // Filtrar proveedores no eliminados

        // Si hay un término de búsqueda, lo aplicamos en las columnas
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereRaw("LOWER(p.nombre_razonsocial) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.apellidos_nombrecomercial) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.tipo_documento) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.numero_documento) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.celular) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.direccion) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.distrito) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.provincia) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.departamento) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.email) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.tipo_entidad_sunat) LIKE ?", ["%{$q}%"])
                    ->orWhereRaw("LOWER(p.estado) LIKE ?", ["%{$q}%"]);
            });
        }

        // Ordenar los resultados
        $query->orderBy($sort, $dir);

        // Paginación
        $proveedores = $query->paginate($perPage, ['*'], 'page', $page);

        // Formatear los resultados antes de devolverlos
        $proveedores->getCollection()->transform(function ($proveedor) {
            return [
                'idpersona'             => $proveedor->idpersona,
                'idtipoestandarproveedor'=> $proveedor->idtipoestandarproveedor,
                'tipoPersona'           => $proveedor->tipoPersona,
                'tipo_documento'        => $proveedor->tipo_documento,
                'nombre_razonsocial'    => $proveedor->nombre_razonsocial,
                'apellidos_nombrecomercial' => $proveedor->apellidos_nombrecomercial,
                'abreviatura'           => $proveedor->abreviatura,
                'numero_documento'     => $proveedor->numero_documento,
                'celular'               => $proveedor->celular,
                'direccion'             => $proveedor->direccion,
                'distrito'              => $proveedor->distrito,
                'provincia'             => $proveedor->provincia,
                'departamento'          => $proveedor->departamento,
                'email'                 => $proveedor->email,
                'tipo_entidad_sunat'    => $proveedor->tipo_entidad_sunat,
                'tipo_estandar'         => $proveedor->tipo_estandar,
                'nroDocumentos'         => $proveedor->nroDocumentos,
                'estado'                => $proveedor->estado,
            ];
        });

        // Devolver la respuesta JSON con los resultados
        return response()->json([
            'data'         => $proveedores->items(),
            'current_page' => $proveedores->currentPage(),
            'per_page'     => $proveedores->perPage(),
            'total'        => $proveedores->total(),
            'last_page'    => $proveedores->lastPage(),
            'from'         => $proveedores->firstItem(),
            'to'           => $proveedores->lastItem(),
            'sort'         => $sort,
            'dir'          => $dir,
            'q'            => $q,
        ]);
    }

    // Método para obtener todos roles personas
    public function selec2tipoEstandar()
    {
      try {
        $data  = Tipo_estandar::select2tipoestandar();

        $options = ''; // string para concatenar HTML
        foreach ($data as $t) {
            $options .= '<option value="'.$t->idtipoestandarproveedor.'" >' . e($t->descripcion). '</option>';
        }

        return ApiResponse::success($options, 'Tipo Estandar obtenida');

      } catch (\Throwable $e) {
          return ApiResponse::error($e);
      }

    }

    // listar tipos de estandar y documentos asociados
    public function listar_tipos_estandar_docs(Request $r)
    {
        try {
            $id = $r->input('idtipoestandar');
            $idpersona = $r->input('idpersona');

        // Crear la consulta base idtipoestandar: id , idpersona
        $data = DB::table('tipoestandarproveedor as est')
            ->join('detalletipoestandarproveedor as det', 'est.idtipoestandarproveedor', '=', 'det.idtipoestandarproveedor')
            ->join('persona as p', 'p.idtipoestandarproveedor', '=', 'est.idtipoestandarproveedor')
            ->leftJoin('docsproveedortipoestandar as doc', 'det.iddetalletipoestandarproveedor', '=', 'doc.iddetalletipoestandarproveedor')
            ->select(
                'det.iddetalletipoestandarproveedor',
                'det.idtipoestandarproveedor',
                'det.detalle',
                'det.estado_trash',
                'det.estado_delete',
                'doc.nombreDocumento',
                'doc.archivo',
                'doc.estado_trash as estado_trashDocs',
                'doc.estado_delete as estado_deleteDocs'
            )
            ->where('est.idtipoestandarproveedor', $id)
            ->where('p.idpersona', $idpersona) 
                  ->get()
            ->map(function ($row) {
                return [
                'iddetalletipoestandarproveedor' => $row->iddetalletipoestandarproveedor,
                'idtipoestandarproveedor'        => $row->idtipoestandarproveedor,
                'detalle'                        => $row->detalle,
                'estado_trash'                   => $row->estado_trash,
                'estado_delete'                  => $row->estado_delete,
                'nombreDocumento'                => $row->nombreDocumento,
                'archivo'                        => $row->archivo,
                'estado_trashDocs'               => $row->estado_trashDocs,
                'estado_deleteDocs'              => $row->estado_deleteDocs,
                ];
            });

        return ApiResponse::success($data, 'Tipos de estandar y documentos obtenidos');

        } catch (\Throwable $e) {
            return ApiResponse::error($e);
        }




        
    }


}
