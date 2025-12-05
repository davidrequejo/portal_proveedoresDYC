<?php

use App\Http\Controllers\CombinarExcelAfpController;
use App\Http\Controllers\CombinarTxtController;
use App\Http\Controllers\ImportarHoraDetalleObertiController;
use App\Http\Controllers\ImportarHoraObertiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\PresupuestoDetalleController;
use App\Http\Controllers\PresupuestoGrupoController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\RecursoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PersonaController;
Use App\Http\Controllers\ApiReniecSunatController;
use App\Http\Controllers\UbigeoDistritoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Tipo_estandarController;


Route::get('/', function () {  return redirect()->route('login'); });

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::get('/dashboard', function () {  return view('inicio');   })->name('dashboard');


    // :::::::::::::::::::::::::::::: I N I C I O ::::::::::::::::::::::::::::::
    Route::get('/inicio', function () {  return view('inicio');   })->name('inicio');

    // :::::::::::::::::::::::::::::: P R O Y E C T O ::::::::::::::::::::::::::::::
    Route::post('/proyectos/crear_proyecto', [ProyectoController::class, 'crear_proyecto'])->name('proyectos.crear_proyecto');                     // crear
    Route::put('/proyectos/editar_proyecto/{idproyecto}', [ProyectoController::class, 'editar_proyecto'])->whereNumber('idproyecto')->name('proyectos.editar_proyecto'); // editar
    Route::get('/proyectos/detalle-html/{idproyecto}', [ProyectoController::class, 'detalleHtml'])->name('proyectos.detalleHtml');

    Route::get('/proyectos/tabla_principal', [ProyectoController::class, 'tabla_principal'])->name('proyectos.datatable'); // AJAX
    Route::get('/proyectos/{idproyecto}/ver-editar', [ProyectoController::class, 'ver_editar_proyecto'])->whereNumber('idproyecto')->name('proyectos.ver_editar');
    Route::resource('proyectos', ProyectoController::class);

        // ::::::::::::::::: PERSONAS SOCIO NEGOCIO ::::::::::::::::::::::::::::::
    Route::post('/persona/crear_persona', [PersonaController::class, 'crear_persona'])->name('persona.crear_persona');                     // crear
    Route::get('/persona/tabla_principal', [PersonaController::class, 'Listar_personas'])->name('persona.Listar_personas'); // AJAX
    Route::get('/select2/Rolpersona', [PersonaController::class, 'selec2Rolpersona']);
    Route::get('/persona/{idpersona}/ver-editar', [PersonaController::class, 'mostrar_editar_persona'])->whereNumber('idpersona')->name('persona.mostrar_editar_persona'); //mostar para editar
    Route::put('/persona/editar_persona/{idpersona}', [PersonaController::class, 'editar_persona'])->whereNumber('idpersona')->name('persona.editar_persona'); // editar
    Route::put('/persona/eliminar_persona/{idpersona}', [PersonaController::class, 'eliminar_persona'])->whereNumber('idpersona')->name('persona.eliminar_persona'); // eliminar
    Route::resource('persona', PersonaController::class);


    //:::::::::::::::::::::::::. TIPO ESTANDAR ::::::::::::::::::::::::::::::
    Route::post('/tipoestandar/crear_tipoestandar', [Tipo_estandarController::class, 'crear_tipoestandar'])->name('tipoestandar.crear_tipoestandar');                     // crear
    Route::get('/tipoestandar/tabla_principal', [Tipo_estandarController::class, 'Listar_tipoestandar'])->name('tipoestandar.Listar_tipoestandar'); // AJAX
    Route::get('/tipoestandar/{idtipoestandarproveedor}/ver-editar', [Tipo_estandarController::class, 'mostrar_tipoestandar'])->whereNumber('idtipoestandarproveedor')->name('tipoestandar.mostrar_tipoestandar'); //mostar para editar
    Route::put('/tipoestandar/editar_tipoestandar/{idtipoestandarproveedor}', [Tipo_estandarController::class, 'editar_tipoestandar'])->whereNumber('idtipoestandarproveedor')->name('tipoestandar.editar_tipoestandar'); // editar
    Route::put('/tipoestandar/eliminar_tipoestandar/{idtipoestandarproveedor}', [Tipo_estandarController::class, 'eliminar_tipoestandar'])->whereNumber('idtipoestandarproveedor')->name('tipoestandar.eliminar_tipoestandar'); // eliminar
    Route::resource('tipo_estandar', Tipo_estandarController::class);

    
    // :::::::::::::::::::::::::::::: P R O V E E D O R E S ::::::::::::::::::::::::::::::
    Route::post('/proveedor/crear_proveedor', [ProveedorController::class, 'crear_proveedor'])->name('proveedor.crear_proveedor');                     // crear
    //Route::put('/proyectos/editar_proyecto/{idproyecto}', [ProyectoController::class, 'editar_proyecto'])->whereNumber('idproyecto')->name('proyectos.editar_proyecto'); // editar
    //Route::get('/proyectos/detalle-html/{idproyecto}', [ProyectoController::class, 'detalleHtml'])->name('proyectos.detalleHtml');

    Route::get('/proveedor/tabla_principal', [ProveedorController::class, 'Listar_Proveedores'])->name('proveedor.Listar_Proveedores'); // AJAX
    //Route::get('/proyectos/{idproyecto}/ver-editar', [ProyectoController::class, 'ver_editar_proyecto'])->whereNumber('idproyecto')->name('proyectos.ver_editar');
    Route::resource('proveedor', ProveedorController::class);

    // :::::::::::::::::::::::::::::: API SUNAT RENIEC ::::::::::::::::::::::::::::::
    Route::post('/consulta/reniec', [ApiReniecSunatController::class, 'buscarReniec']);
    Route::post('/consulta/sunat', [ApiReniecSunatController::class, 'buscarSunat']);
    // :::::::::::::::: S E L E C T 2   U B I G E O  D I S T R I T O  :::::::::::::::::::::
    Route::get('/select2/obtener', [UbigeoDistritoController::class, 'obtenerDistritos']);
    //:::::::::::::::::.:.::::::::::::::::::: usuarios  ::::::::::::::::::::::::::::::
    Route::post('/persona/crear_usuario', [UsuarioController::class, 'crear_usuario'])->name('persona.crear_usuario');  
    Route::get('/usuario/tabla_principal', [UsuarioController::class, 'Listar_usuarios'])->name('usuario.Listar_usuarios'); // AJAX
    Route::get('/usuario/permisos_crear', [UsuarioController::class, 'MostrarPermisos_crear'])->name('usuario.MostrarPermisos_crear');   // ← NUEVO
    Route::get('/select2/socionegocio', [UsuarioController::class, 'select2pers_sin_user']); //  ← select2 personas sin usuario
    
    Route::resource('usuario', UsuarioController::class);

    



    // :::::::::::::::::::::::::::::: P R E S U P U E S T O S ::::::::::::::::::::::::::::::
    Route::post('/presupuestos/crear_cabecera',                         [PresupuestoController::class, 'crear_cabecera_presupuesto'])->name('presupuestos.crear.cabecera');
     Route::put('/presupuestos/{idpresupuesto}/actualizar_cabecera',    [PresupuestoController::class, 'actualizar_cabecera_presupuesto'])->name('presupuestos.actualizar_cabecera');    
     Route::get('/presupuestos/{idpresupuesto}/mostrar',                [PresupuestoController::class, 'mostrar_editar'])->name('presupuestos.mostrar');
    Route::resource('presupuestos', PresupuestoController::class);
    
    
    // :::::::::::::::::::::::::::::: R E C U R S O S  ::::::::::::::::::::::::::::::
    Route::get('/recursos/listar_recursos_x_niveles',       [RecursoController::class, 'getlistar_recursos_x_nivel'])->name('recursos.getlistar_recursos_x_nivel');
    Route::get('/recursos/listar_recursos_ultimo_nivel',    [RecursoController::class, 'getlistar_recursos_ultimo_nivel'])->name('recursos.getlistar_recursos_ultimo_nivel');
    Route::resource('recursos', RecursoController::class);

    // :::::::::::::::::::::::::::::: G R U P O   P R E S U P U E S T O   ::::::::::::::::::::::::::::::
    Route::get('/arbol-presupuestos/jstree',                [PresupuestoGrupoController::class, 'arbolCompleto'])->name('arbol.presupuestos.jstree');
    Route::post('/grupos/crear',                            [PresupuestoGrupoController::class, 'crear'])->name('grupo.crear');
    Route::put('/grupos/{idpresupuesto_grupo}/actualizar',  [PresupuestoGrupoController::class, 'actualizar_grupo'])->name('grupo.actualizar');    
    Route::get('/grupos/{idpresupuesto_grupo}/mostrar',     [PresupuestoGrupoController::class, 'mostrar_editar'])->name('grupo.mostrar');
    Route::delete('/grupos/{id}',                           [PresupuestoGrupoController::class, 'destroy'])->name('grupo.delete');
    


    // :::::::::::::::::::::::::::::: EXCEL OBERTI  ::::::::::::::::::::::::::::::
    Route::post('/horas/plantilla/llenar',  [ImportarHoraObertiController::class, 'fillTemplate'])->name('horas.template.fill');
    Route::get('/horas/cabeceras',          [ImportarHoraObertiController::class, 'listCabeceras'])->name('horas.cabeceras'); // JSON con paginación + búsqueda
    Route::get('/horas/mostrar_detalle',    [ImportarHoraObertiController::class, 'mostrar_detalle_hora'])->name('horas.mostrar_detalle_hora'); // JSON con paginación + búsqueda
    Route::post('/horas/preview',           [ImportarHoraObertiController::class, 'preview'])->name('horas.preview');   // ← NUEVO
    Route::post('/horas/importar',          [ImportarHoraObertiController::class, 'import'])->name('horas.import');
    Route::delete('/horas/cabeceras/{idregistro_horas}', [ImportarHoraObertiController::class, 'destroy'])->name('horas.cabeceras.destroy');
    Route::patch( '/registro-horas-detalle/{detalle}/celda',  [ImportarHoraDetalleObertiController::class, 'actualizarCelda'])->name('registro_horas_detalle.actualizar_celda');
    Route::resource('importar-horas',  ImportarHoraObertiController::class);    

    // :::::::::::::::::::::::::::::: COMBINAR TXT  ::::::::::::::::::::::::::::::
   
    Route::post('combinar-txt/guardar_txt', [CombinarTxtController::class, 'guardar_txt'])->name('combinar-txt.guardar'); // Ruta para guardar los archivos   
    Route::get('combinar-txt/mostrar_lista', [CombinarTxtController::class, 'mostrar_lista'])->name('combinar-txt.mostrar'); // Ruta para mostrar la lista combinada    
    Route::get('combinar-txt/descargar/{formato}', [CombinarTxtController::class, 'descargar_combinado'])->name('combinar-txt.descargar');// Ruta para descargar el archivo combinado
    Route::resource('combinar-txt',  CombinarTxtController::class);    


    // :::::::::::::::::::::::::::::: COMBINAR PLANILLA AFP EXCEL  ::::::::::::::::::::::::::::::

    Route::post('planilla-afp/import', [CombinarExcelAfpController::class, 'import'])->name('planilla-afp.import');
    Route::get('planilla-afp/mostrar_lista', [CombinarExcelAfpController::class, 'mostrar_lista'])->name('combinar-txt.mostrar'); // Ruta para mostrar la lista combinada    
    Route::get('planilla-afp/descargar/excel', [CombinarExcelAfpController::class, 'descargar_excel']) ->name('planilla-afp.descargar_excel');    
    Route::resource('combinar-planilla-afp', CombinarExcelAfpController::class);

    // :::::::::::::::::::::::::::::: S E L E C T 2 ::::::::::::::::::::::::::::::
    Route::get('/select2/select2tipoelementos', [RecursoController::class, 'getselect2TipoElementos']);
    
});
