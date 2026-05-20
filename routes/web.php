<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PersonaController;
Use App\Http\Controllers\ApiReniecSunatController;
use App\Http\Controllers\UbigeoDistritoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\Tipo_estandarController;
use App\Http\Controllers\SubirDocsController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\PersonaCuentaBancariaController;
use App\Http\Controllers\ActualizardatosproveedorController;
use App\Http\Controllers\ActualizardatosclienteController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\HomologacionController;
use App\Http\Controllers\DocumentoTipoEstandarController;
use App\Http\Controllers\NotificacionController; 
use App\Http\Controllers\AllHomologacionesController; 
use App\Http\Controllers\AreaPersonaController;
use App\Http\Controllers\ProveedorLogController;
use App\Http\Controllers\ApiSincronizarS10;

//EVALUACION
use App\Http\Controllers\PlantillaEvaluacionController;
use App\Http\Controllers\SeleccEvaluacionController;



Route::get('/', function () {  return redirect()->route('login'); });

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::get('/dashboard', function () {  return view('inicio');   })->name('dashboard');

    // :::::::::::::::::::::::::::::: I N I C I O ::::::::::::::::::::::::::::::
     Route::resource('inicio', InicioController::class);
    //:.:::::::::::::::::: MNUAL DE USUARIO ::::::::::::::::::::::::::::::
     Route::get('/manual_usuario', function () {  return view('manual_usuario');   })->name('manual_usuario');

    // ::::::::::::::::: PERSONAS SOCIO NEGOCIO ::::::::::::::::::::::::::::::
    Route::post('/persona/crear_persona', [PersonaController::class, 'crear_persona'])->name('persona.crear_persona');                     // crear
    Route::get('/persona/tabla_principal', [PersonaController::class, 'Listar_personas'])->name('persona.Listar_personas'); // AJAX
    Route::get('/select2/Rolpersona', [PersonaController::class, 'selec2Rolpersona']);
    Route::get('/select2/Areapersona', [PersonaController::class, 'selec2areapersona']);
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
    Route::get('/tipoestandar/select2docstipoestandar', [Tipo_estandarController::class, 'select2DocumentoTipoEstandar']);
    
    Route::resource('tipo_estandar', Tipo_estandarController::class);

    //:::::::::::::::::::: DOCUMENTO TIPO ESTÁNDAR :::::::::::::::::::::::
    Route::post('/documento-tipo-estandar/crear_conf_docs', [DocumentoTipoEstandarController::class, 'crear_documento_tipo_estandar'])->name('documento_tipo_estandar.crear');
    Route::get('/documento-tipo-estandar/tabla_principal_conf_docs', [DocumentoTipoEstandarController::class, 'listar_documento_tipo_estandar'])->name('documento_tipo_estandar.listar');
    Route::get('/documento-tipo-estandar/{id}/ver-editar_conf_docs', [DocumentoTipoEstandarController::class, 'mostrar_documento_tipo_estandar'])->whereNumber('id')->name('documento_tipo_estandar.mostrar');
    Route::put('/documento-tipo-estandar/editar_conf_docs/{id}', [DocumentoTipoEstandarController::class, 'editar_documento_tipo_estandar'])->whereNumber('id')->name('documento_tipo_estandar.editar');
    Route::put('/documento-tipo-estandar/eliminar_conf_docs/{id}', [DocumentoTipoEstandarController::class, 'eliminar_documento_tipo_estandar'])->whereNumber('id')->name('documento_tipo_estandar.eliminar');
    Route::resource('documento-tipo-estandar', DocumentoTipoEstandarController::class);
        
    // :::::::::::::::::::::::::::::: P R O V E E D O R E S :::::::::::::::::::::::::::::: 
    Route::post('/proveedor/crear_proveedor', [ProveedorController::class, 'crear_proveedor'])->name('proveedor.crear_proveedor');                     // crear
    Route::get('/proveedor/{idpersona}/mostrar_proveedor', [ProveedorController::class, 'mostrar_proveedor'])->whereNumber('idpersona')->name('proveedor.mostrar_proveedor'); //mostar para editar
    Route::put('/proveedor/editar_proveedor/{idpersona}', [ProveedorController::class, 'editar_proveedor'])->whereNumber('idpersona')->name('proveedor.editar_proveedor'); // editar
    Route::put('/proveedor/eliminar_proveedor/{idpersona}', [ProveedorController::class, 'eliminar_proveedor'])->whereNumber('idpersona')->name('proveedor.eliminar_proveedor'); // eliminar
    Route::get('/proveedor/tabla_principal', [ProveedorController::class, 'Listar_Proveedores'])->name('proveedor.Listar_Proveedores'); // AJAX
    Route::get('/proveedor/ver_listar_tipos_estandar_docs', [ProveedorController::class, 'listar_tipos_estandar_docs'])->name('proveedor.listar_tipos_estandar_docs'); //mostar para editar

    Route::post('/proveedor/importar_proveedores_excel', [ProveedorController::class, 'ImportarProveedoresExcel'])->name('proveedor.ImportarProveedoresExcel'); // importar masivo desde excel

    Route::get('/select2/tipoestandar', [ProveedorController::class, 'selec2tipoEstandar']); //  ← select2  
    Route::get('/select2/periodohomologacion', [ProveedorController::class, 'selec2periodohomologacion']); //  ← select2  
    Route::get('/select2/pers_compr_adm', [ProveedorController::class, 'select2pers_compr_adm']); //  ← select2  
    Route::resource('proveedor', ProveedorController::class);

    //:::::::::::::::::::::::::. FECHA HOMOLOGACIÓN :::::::::::::::::::::::::::::: 
    Route::post('/homologacion/crear_periodo_homologacion', [HomologacionController::class, 'crear_periodo_homologacion'] )->name('homologacion.crear_periodo_homologacion'); // crear
    Route::get('/homologacion/tabla_periodo_h_principal', [HomologacionController::class, 'listar_periodo_homologacion'] )->name('homologacion.listar_periodo_homologacion'); // AJAX
    Route::get('/homologacion/{id}/mostrar_periodo_homologacion', [HomologacionController::class, 'mostrar_periodo_homologacion'] )->whereNumber('id')->name('homologacion.mostrar_periodo_homologacion'); // mostrar para editar
    Route::put('/homologacion/editar_periodo_homologacion/{id}',[HomologacionController::class, 'editar_periodo_homologacion'] )->whereNumber('id')->name('homologacion.editar_periodo_homologacion'); // editar
    Route::put('/homologacion/establecerfechas_periodo_homologacion/{id}',[HomologacionController::class, 'establecerfechas_periodo_homologacion'] )->whereNumber('id')->name('homologacion.establecerfechas_periodo_homologacion'); // editar
    Route::put('/eliminar_periodo_h/eliminar_eliminar_periodo_h/{id}', [HomologacionController::class, 'eliminar_eliminar_periodo_h'])->whereNumber('id')->name('homologacion.eliminar_eliminar_periodo_h'); // eliminar
    Route::get('/homologacion/listar_docs_xperiodo_xproveedor', [HomologacionController::class, 'listar_docs_xperiodo_xproveedor'] )->name('homologacion.listar_docs_xperiodo_xproveedor'); // AJAX
    Route::put('/homologacion/actualizar_estado_doc_estandar/{id}', [HomologacionController::class, 'actualizar_estado_doc_estandar'])->whereNumber('id')->name('homologacion.actualizar_estado_doc_estandar'); // editar
    Route::put('/homologacion/cargar_documento_interno_estandar/{id}', [HomologacionController::class, 'cargar_documento_interno_estandar'])->whereNumber('id')->name('homologacion.cargar_documento_interno_estandar'); // editar
    
    Route::post('/homologacion/enviar_correo_notificacion', [HomologacionController::class, 'enviar_correo_notificacion'])->name('homologacion.enviar_correo_notificacion'); // enviar correo notificación
    Route::get('/homologacion/{id}/ultimo-envio', [HomologacionController::class, 'show_ultimo_envio_notificacion'])->name('homologacion.ultimo-envio');
    Route::resource('homologacion', HomologacionController::class );


    // :::::::::::::::::::::::::::::: API SUNAT RENIEC ok ::::::::::::::::::::::::::::::
    Route::post('/consulta/reniec', [ApiReniecSunatController::class, 'buscarReniec']);
    Route::post('/consulta/sunat', [ApiReniecSunatController::class, 'buscarSunat']);

    // :::::::::::::::: S E L E C T 2   U B I G E O  D I S T R I T O ok  :::::::::::::::::::::
    Route::get('/select2/obtener', [UbigeoDistritoController::class, 'obtenerDistritos']);

    //:::::::::::::::::.:.::::::::::::::::::: usuarios ok  ::::::::::::::::::::::::::::::
    Route::post('/persona/crear_usuario', [UsuarioController::class, 'crear_usuario'])->name('persona.crear_usuario');  
    Route::get('/usuario/tabla_principal', [UsuarioController::class, 'Listar_usuarios'])->name('usuario.Listar_usuarios'); // AJAX
    Route::get('/usuario/{id}/ver-editar_usuario', [UsuarioController::class, 'mostrar_usuario_editar'])->whereNumber('id')->name('usuario.mostrar_usuario_editar'); //mostar para editar
    Route::put('/usuario/editar_usuario/{id}', [UsuarioController::class, 'editar_usuario'])->whereNumber('id')->name('usuario.editar_usuario'); // editar
    Route::put('/usuario/eliminar_usuario/{id}', [UsuarioController::class, 'eliminar_usuario'])->whereNumber('id')->name('tipoestandar.eliminar_usuario'); // eliminar
    Route::get('/usuario/permisos_crear', [UsuarioController::class, 'MostrarPermisos_crear'])->name('usuario.MostrarPermisos_crear');   // ← NUEVO
    Route::get('/select2/socionegocio', [UsuarioController::class, 'select2pers_sin_user']); //  ← select2 personas sin usuario
    
    Route::resource('usuario', UsuarioController::class);

    //:::::::::::::::::::::::::. BANCO ::::::::::::::::::::::::::::::
    Route::post('/banco/crear_banco', [BancoController::class, 'crear_banco'])->name('banco.crear_banco'); // crear
    Route::get('/banco/tabla_principal', [BancoController::class, 'listar_banco'])->name('banco.listar_banco'); // AJAX
    Route::get('/banco/{idbanco}/ver-editar', [BancoController::class, 'mostrar_banco'])->whereNumber('idbanco')->name('banco.mostrar_banco'); // mostrar para editar
    Route::put('/banco/editar_banco/{idbanco}', [BancoController::class, 'editar_banco'])->whereNumber('idbanco')->name('banco.editar_banco'); // editar
    Route::put('/banco/eliminar_banco/{idbanco}', [BancoController::class, 'eliminar_banco'])->whereNumber('idbanco')->name('banco.eliminar_banco'); // eliminar
    Route::resource('banco', BancoController::class);

    //::::::::::::::::::::::. PERSONA CUENTA BANCARIA :::::::::::::::::::::::
    Route::post( '/persona-cuenta-bancaria/crear', [PersonaCuentaBancariaController::class, 'crear'])->name('persona_cuenta_bancaria.crear');
    Route::get( '/persona-cuenta-bancaria/tabla_principal', [PersonaCuentaBancariaController::class, 'listar'])->name('persona_cuenta_bancaria.listar');
    Route::get( '/persona-cuenta-bancaria/{id}/ver-editar', [PersonaCuentaBancariaController::class, 'mostrar'])->whereNumber('id') ->name('persona_cuenta_bancaria.mostrar');
    Route::put( '/persona-cuenta-bancaria/editar/{id}', [PersonaCuentaBancariaController::class, 'editar'])->whereNumber('id') ->name('persona_cuenta_bancaria.editar');
    Route::put( '/persona-cuenta-bancaria/eliminar/{id}', [PersonaCuentaBancariaController::class, 'eliminar'])->whereNumber('id') ->name('persona_cuenta_bancaria.eliminar'); 
     Route::get( '/select2/bancos', [PersonaCuentaBancariaController::class, 'selec2banco']); //  ← select2 bancos
    Route::resource( 'persona-cuenta-bancaria', PersonaCuentaBancariaController::class);

     // :::::::::::::::::::::::::::::: A C T U A L I Z A R   D A T O S   P R O V E E D O  R :::::::::::::::::::::::::::::: 
    Route::get('/actualizardatosproveedor/{id}/ver_proveedorupdate', [ActualizardatosproveedorController::class, 'ver_proveedorupdate'])->whereNumber('id')->name('actualizardatosproveedor.ver_proveedorupdate'); //mostar para editar
    route::put('/actualizardatosproveedor/editarProveedor', [ActualizardatosproveedorController::class, 'editarProveedor'])->name('actualizardatosproveedor.editarProveedor'); // editar
    Route::resource('actualizardatos', ActualizardatosproveedorController::class);

    // :::::::::::::::::::::::::::::: A C T U A L I Z A R   D A T O S  C L I E N T E :::::::::::::::::::::::::::::: 
    Route::get('/actualizardatoscliente/{id}/ver_clienteupdate', [ActualizardatosclienteController::class, 'ver_clienteupdate'])->whereNumber('id')->name('actualizardatoscliente.ver_clienteupdate'); //mostar para editar
    route::put('/actualizardatoscliente/editarcliente', [ActualizardatosclienteController::class, 'editarcliente'])->name('actualizardatoscliente.editarcliente'); // editar
    Route::resource('actualizardatoscliente', ActualizardatosclienteController::class);


    // :::::::::::::::::::::::::::::: S U B I R   D O C U M E N T O S  ::::::::::::::::::::::::::::::  periodo_homologacion_xpersona
 
    Route::post('/subir_docs/guardar_doc_estandar_proveedor', [SubirDocsController::class, 'guardar_doc_estandar_proveedor'])->name('subir_docs.guardar_doc_estandar_proveedor'); //Create 
    Route::get('/subir_docs/ver_doc_estandar/{id}', [SubirDocsController::class, 'ver_doc_estandar'])->whereNumber('id')->name('subir_docs.ver_doc_estandar'); //mostar para editar
    Route::put('/subir_docs/editar_doc_estandar_proveedor/{id}', [SubirDocsController::class, 'editar_doc_estandar_proveedor'])->whereNumber('id')->name('subir_docs.editar_doc_estandar_proveedor'); // editar
    Route::get('/subir_docs/listar_docs_tipos_est_xuser', [SubirDocsController::class, 'listar_docs_tipos_est_xuser'])->name('subir_docs.listar_docs_tipos_est_xuser'); // AJAX
    Route::get('/subir_docs/periodo_homologacion', [SubirDocsController::class, 'periodo_homologacion_xpersona']); //  ← Lista inicial 
    Route::resource('subir_docs', SubirDocsController::class);
   
    // :::::::::::::::::::::::::::::: REGISTRO DE CLIENTES DEL CLIENTE  :::::::::::::::::::::::::::::: 

    Route::post('/cliente/crear_cliente', [ClienteController::class, 'crear_cliente'])->name('cliente.crear_cliente'); // crear
    Route::get('/cliente/{idpersona}/mostrar_cliente', [ClienteController::class, 'mostrar_cliente'])->whereNumber('idpersona')->name('cliente.mostrar_cliente'); //mostar para editar
    Route::put('/cliente/editar_cliente/{idpersona}', [ClienteController::class, 'editar_cliente'])->whereNumber('idpersona')->name('cliente.editar_cliente'); // editar
    Route::put('/cliente/eliminar_cliente/{idpersona}', [ClienteController::class, 'eliminar_cliente'])->whereNumber('idpersona')->name('cliente.eliminar_cliente'); // eliminar
    Route::get('/cliente/tabla_principal', [ClienteController::class, 'Listar_clientes'])->name('cliente.Listar_clientes'); // AJAX

    Route::post('/cliente/importar_clientes_excel', [ClienteController::class, 'ImportarclientesExcel'])->name('cliente.ImportarclientesExcel'); // importar masivo desde excel

    Route::resource('cliente', ClienteController::class);

    //::::::::::::::::::::::::::::::.NOTIFICACIONES:::::::::::::::::::::::::::::::::..
    // marcar una notificación
    Route::get('/notificacion/leer/{id}', [NotificacionController::class, 'leer'] )->name('notificaciones.leer');
    // marcar todas
    Route::get('/notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodas'] )->name('notificaciones.marcarTodas');

    //::::::::::::::::::::::::::::::::.ALL NOTIFICACIONES ::::::::::::::::::::::::::::::: 
     Route::get('/homologaciones/listar_homologaciones_all', [AllHomologacionesController::class, 'listar_homologaciones_all'])->name('all_homologaciones.listar_homologaciones_all'); // AJAX
     Route::get('/select2/compradores_all', [AllHomologacionesController::class, 'select2compradoreshomologacion']); //  ← select2 
     Route::get('/select2/proveedores_all', [AllHomologacionesController::class, 'select2proveedoreshomologacion']); //  ← select2 
     Route::get('/select2/estado_homologacion_all', [AllHomologacionesController::class, 'select2estadohomologacion']); //  ← select2 
     Route::get('/select2/tipoestandar_all', [AllHomologacionesController::class, 'selec2tipoEstandar']); //  ← select2 

     Route::get( 'homologaciones/descargar-documentos/{id}', [AllHomologacionesController::class, 'descargarDocumentos'])->name('homologaciones.descargar.documentos');
     Route::get( 'homologaciones/descarga-masiva', [AllHomologacionesController::class, 'descargaMasiva'])->name('homologaciones.descarga.masiva');

     //Route::get('homologaciones/descarga-masiva', [HomologacionController::class, 'descargaMasiva'])->name('homologaciones.descarga-masiva');
    
     Route::get('homologaciones/verificar-archivos', [AllHomologacionesController::class, 'verificarArchivos'])->name('homologaciones.verificar-archivos');
     Route::get('homologaciones/vista-previa', [AllHomologacionesController::class, 'vistaPrevia']);

     Route::resource('all_homologaciones', AllHomologacionesController::class);

    //:::::::::::::::::::::::::. AREA PERSONA ::::::::::::::::::::::::::::::
      Route::post('/area_persona/crear_area_persona', [AreaPersonaController::class, 'crear_area_persona'])->name('area_persona.crear_area_persona'); // crear
      Route::get('/area_persona/tabla_principal', [AreaPersonaController::class, 'listar_area_persona'])->name('area_persona.listar_area_persona'); // AJAX
      Route::get('/area_persona/{idarea_persona}/ver-editar', [AreaPersonaController::class, 'mostrar_area_persona'])->whereNumber('idarea_persona')->name('area_persona.mostrar_area_persona'); // mostrar para editar
      Route::put('/area_persona/editar_area_persona/{idarea_persona}', [AreaPersonaController::class, 'editar_area_persona'])->whereNumber('idarea_persona')->name('area_persona.editar_area_persona'); // editar
      Route::put('/area_persona/eliminar_area_persona/{idarea_persona}', [AreaPersonaController::class, 'eliminar_area_persona'])->whereNumber('idarea_persona')->name('area_persona.eliminar_area_persona'); // eliminar
      // Select2
      Route::get('/area_persona/select2', [AreaPersonaController::class, 'select2_area_persona'])->name('area_persona.select2');

      //::::::::::::::::::::::. Log proveedor / Cuenta Bancaria ::::::::::::::::::::::: vercuentasbancariaslogproveedor
      Route::get('/logController/verdatosproveedor/{id?}', [ProveedorLogController::class, 'verdatoslogproveedor'])->name('ProveedorLogController.verdatoslogproveedor');
        Route::get('/logController/vercuentasbancariasproveedor/{id?}', [ProveedorLogController::class, 'vercuentasbancariaslogproveedor'])->name('ProveedorLogController.vercuentasbancariaslogproveedor');

      //:::::::::::::::::::::::API PARA CONECTAR S10 :::::::::::::::::::::::::::::::::::::::::.
      Route::post('/proveedores/{proveedor}/sincronizar-s10/{idlogbd?}', [ApiSincronizarS10::class, 'sincronizar'])->name('proveedores.sincronizar');
      Route::post('/proveedores/{proveedor}/sincronizarcb-s10/{idlogbd?}', [ApiSincronizarS10::class, 'sincronizarCuentasBancarias'])->name('proveedores.sincronizarCuentasBancarias');
      
      
       Route::get('/pruebas10api', function () {  return view('pruebas10api');   })->name('pruebas10api');

      // (opcional) resource si lo usas como en Banco
      Route::resource('area_persona', AreaPersonaController::class);

      //:::::::::::::::::::::::::. PLANTILLAS EVALUACIÓN ::::::::::::::::::::::::::::::
      Route::resource('plantilla_evaluacion', PlantillaEvaluacionController::class);

      //:::::::::::::::::::::::::. SELECCIÓN EVALUACIÓN ::::::::::::::::::::::::::::::
      Route::get('/select2/personas_selec_evaluacion', [SeleccEvaluacionController::class, 'select2PersonaselecEvaluacion']); //  ← select2 
      Route::resource('selecc_evaluacion', SeleccEvaluacionController::class);
      

      Route::get('/test-modelo', function() {
        try {
            $noti = App\Models\NotificacioncorreoenviadorevisionDocsH::create([
                'homologacion_id' => 1,
                'estado' => 'test',
                'fecha_envio' => now(),
                'observacion' => 'Test manual'
            ]);
            
            return "✅ Insertado ID: " . $noti->id;
        } catch (\Exception $e) {
            return "❌ Error: " . $e->getMessage();
        }
      });
});
