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
