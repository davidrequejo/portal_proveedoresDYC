<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Tipo Estandar | Portal Proveedores D&C</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/dc-logo.png') }}" type="image/png">

  @include('layouts.lte_head')
  <!--<link rel="stylesheet" href="{{ asset('assets/jstree-3.3.17/dist/themes/default/style.min.css') }}" />-->

  <style>
    #tabla-tipo_estandar_filter { width: calc(100% - 10px) !important; display: flex !important; justify-content: space-between !important; }
    #tabla-tipo_estandar_filter label { width: 100% !important;  }
    #tabla-tipo_estandar_filter label input { width: 100% !important;   }

    /* Indicadores de orden simple (opcional) */
    th.sortable { cursor:pointer; position:relative; }
    th.sortable.asc::after  { content:"▲"; font-size:.7rem; position:absolute; right:.4rem; }
    th.sortable.desc::after { content:"▼"; font-size:.7rem; position:absolute; right:.4rem; }

    .fila-proyecto.selected {  background-color: #e7f1ff !important; }
    .fila-proyecto-presupuesto.selected {  background-color: #e7f1ff !important; }

    .sin-borde { border: none !important; border-bottom: 1px solid #bfc4c9 !important; background: transparent !important; box-shadow: none !important;}

    #tabla_documentos tbody tr td { padding-top: 2px !important; padding-bottom: 2px !important;}
    #tabla_documentos thead tr th { padding-top: 2px !important; padding-bottom: 2px !important;}

    #tabla_documentos tbody input.form-control { height: 24px !important; padding: 1px 4px !important; font-size: 13px !important;}

    /* Estado normal */
    /* ===== DROPDOWN ===== */

    /* Permitir salto de línea */
    .select2-results__option {
      white-space: pre-line;
      font-size: 13px;
      color: #6c757d; /* gris normal */
    }

    /* Primera línea */
    .select2-results__option::first-line {
      font-size: 14px;
      font-weight: 600;
      color: inherit;
    }

    /* Hover (cuando pasas el mouse) */
    .select2-results__option--highlighted {
      background-color: #6f42c1; /* morado */
      color: #ffffff !important;
    }

    .select2-results__option--highlighted::first-line {
      color: #ffffff !important;
    }

    /* 👈 CUANDO YA ESTÁ SELECCIONADO */
    .select2-results__option[aria-selected="true"] {
      background-color: #e9ecef !important; /* gris claro */
      color: #6c757d !important;           /* texto gris */
    }

    /* Primera línea cuando está seleccionado */
    .select2-results__option[aria-selected="true"]::first-line {
      color: #6c757d !important;
    }

    /* Permitir salto de línea en el tag */
    .select2-selection__choice {
      white-space: pre-line;
    }

    /* Primera línea (documento) */
    .select2-selection__choice::first-line {
      font-size: 13px;
      font-weight: 600;
    }

    /* Segunda línea (Tipo) */
    .select2-selection__choice {
      font-size: 11px;
      font-weight: 400;
    }




  </style>

</head>
<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed">
  <div class="wrapper">

    <!-- Preloader -->
    @include('layouts.lte_preloader')

    <!-- Menú contextual personalizado -->
    <div id="menu-contextual-proyecto" style="display:none; position:absolute; z-index:1000;" class="bg-white border rounded shadow-sm shadow-0px-05rem-1rem-rgb-0-0-0-65">      
      <div class="card mb-0">
        <div class="card-header py-2"><span class="font-size-12px text-bold">M Á S - O P C I O N E S</span></div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-p-editar"><i class="ti ti-edit"></i> Editar</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-p-ver-detalle"><i class="ti ti-eye-cog"></i> Ver Detalle</a></li>            
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-p-enviar-terminado"><i class="ti ti-folder-cancel"></i>  Enviar a Terminado</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-p-enviar-papelera"><i class="ti ti-folder-bolt"></i>  Enviar a Papelera</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-p-eliminar"><i class="ti ti-trash-x"></i> Eliminar Permanente</a></li>
            
          </ul>
        </div>
        <!-- /.card-body -->
      </div>
    </div>

    <div id="menu-contextual-add-presupuesto" style="display:none; position:absolute; z-index:1000;" class="bg-white border rounded shadow-sm shadow-0px-05rem-1rem-rgb-0-0-0-65">      
      <div class="card mb-0">
        <div class="card-header py-2"><span class="font-size-12px text-bold">M Á S - O P C I O N E S</span></div>
        <div class="card-body p-0">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-ap-agregar"><i class="ti ti-edit"></i> Agregar</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-ap-ver-detalle"><i class="ti ti-eye-cog"></i> Ver Detalle</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-ap-actualizar"><i class="ti ti-rotate-clockwise-2"></i> Actualizar</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-ap-eliminar"><i class="ti ti-trash-x"></i> Eliminar</a></li>            
          </ul>
        </div>
        <!-- /.card-body -->
      </div>
    </div>

    <!-- Navbar -->
    @include('layouts.lte_nav')  
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    @include('layouts.lte_aside')   

    @if (auth()->user()->perm_proveedor_tipo)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
          </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">

              
              <!-- ./col -->
              <div class="col-12 col-sm-12 col-md-6 col-lg-6" >
                <div class="card">
                  <div class="card-header border-0 ">
                          
                    <h3 class="card-title m-2 font-weight-bold text-principal"> Categoria Proveedor
                    </h3>
                    <div class="float-right">

                      <div class="btn-group btn-agregar-proyecto">
                       <button type="button" class="btn btn-primary limpiar_form_tipoestandar" style="border-color: #ffffff !important;" data-toggle="modal" data-target="#modal-agregar-tipoestandar"  ><i class="ti ti-users-plus"></i> Crear nuevo</button>
                      </div>
                    </div>
                    <div class="card-tools m-2"></div>

                  </div>
                  
                  <div class="card-body pb-1">
                    <div class="row mb-2">                    
                      <div class="col">
                        <input type="search" id="buscar_ts" class="form-control form-control-sm" placeholder="Buscar Datos...">
                      </div>
                      <div class="col-auto">
                        <select id="perPage_ts" class="form-select form-select-sm">
                          <option value="5">5</option>
                          <option value="10" selected>10</option>
                          <option value="25" >25</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                        </select>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-info recargar-tabla_tipo_estandar" data-toggle="tooltip" data-original-title="Recargar tabla" ><i class="ti ti-refresh"></i></button>
                      </div>
                    </div>
                    
                    <div class="table-responsive">
                    
                      <table class="table table-bordered table-hover styletabla" id="tabla-tipo_estandar">
                        <thead>
                          <tr>                        
                            <th>Acciones</th>
                            <th data-sort="codigo"      class="sortable">Código</th>
                            <th data-sort="nombre_razonsocial" class="sortable">Descripcion</th>
                            <th data-sort="tipo_entidad_sunat"     class="sortable">Nro Docs </th>
                            <th data-sort="estado" class="sortable">Estado</th>
                            
                          </tr>
                        </thead>
                        <tbody>                     
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer clearfix bg-color-white">
                    <ul class="pagination pagination-sm m-0 float-right" id="paginacion_ts">
                      
                    </ul>
                  </div>
                </div>
              </div>

                            
              <!-- ./col Documentos-->
              <div class="col-12 col-sm-12 col-md-6 col-lg-6" >
                <div class="card">
                  <div class="card-header border-0">
                          
                    <h3 class="card-title m-2 font-weight-bold text-principal text-bold">Configuración de Documentos
                    </h3>
                    <div class="float-right">

                      <div class="btn-group btn-agregar-proyecto">
                        <button type="button" class="btn btn-primary limpiar_form_docs" style="border-color: #2e6da4 !important;" data-toggle="modal" data-target="#modal-agregar-docs" onclick=""><i class="nav-icon fas fa-file"></i> Crear nuevo</button>
                      </div>
                    </div>
                    <div class="card-tools m-2"></div>

                  </div>
                  
                  
                  <div class="card-body pb-1">
                    <div class="row mb-2">                    
                      <div class="col">
                        <input type="search" id="buscar_docs" class="form-control form-control-sm" placeholder="Buscar Datos...">
                      </div>
                      <div class="col-auto">
                        <select id="perPage_docs" class="form-select form-select-sm">
                          <option value="5">5</option>
                          <option value="10" selected>10</option>
                          <option value="25" >25</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                        </select>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-info recargar-tabla-documento" data-toggle="tooltip" data-original-title="Recargar tabla" ><i class="ti ti-refresh"></i></button>
                      </div>
                    </div>
                    
                    <div class="table-responsive">
                    
                      <table class="table table-bordered table-hover styletabla" id="tabladocumento-test">
                        <thead>
                          <tr>                        
                            <th>Acciones</th>
                            <th data-sort="codigo" class="sortable">Tipo</th>
                            <th data-sort="codigo" class="sortable">Descargar</th>
                            <th data-sort="nombre_razonsocial" class="sortable">Descripcion</th>
                            <th data-sort="estado" class="sortable">Estado</th>
                            
                          </tr>
                        </thead>
                        <tbody>                     
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer clearfix bg-color-white">
                    <ul class="pagination pagination-sm m-0 float-right" id="paginacion_docs">
                      
                    </ul>
                  </div>
                </div>
              </div>

            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->


          <div class="modal fade" id="modal-agregar-tipoestandar">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2 bg-color-principal">
                  <h4 class="modal-title text-white">Tipo Estandar</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-white" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-tipoestandar" name="form-agregar-tipoestandar" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">
                      <!-- tipo estandar -->
                      <input type="hidden" name="idtipoestandarproveedor" id="idtipoestandarproveedor" /> 

                      <!-- descripcion -->
                      <div class="col-12 col-sm-12 col-md-9 col-lg-9">
                        <div class="form-group">
                          <label for="Nombre_Apellidos">Descripción <sup class="text-danger">*</sup></label>
                          <input type="text" name="descripcion" class="form-control fs-h-input" id="descripcion"  />
                        </div>
                      </div>    


                      <!-- Nro Documentos -->
                      <div class="col-12 col-sm-12 col-md-3 col-lg-3">
                        <div class="form-group">
                          <i class="fas fa-paint-brush"></i>
                          <label for="nroDocumentos">Nro Documentos</label>
                          <input type="number" name="nroDocumentos" id="nroDocumentos" class="form-control fs-h-input">
                        </div>
                      </div>             


                      <div class="col-12">
                        <div class="form-group">
                          <label>Seleccionar Documentos</label>
                          <div class="select2-purple">
                            <select class="select2" name="selectiddocumento_tipo_estandar[]" id="selectiddocumento_tipo_estandar" multiple="multiple" data-placeholder="Seleccionar" data-dropdown-css-class="select2-purple" style="width: 100%;">
                            </select>
                          </div>
                        </div>
                        <!-- /.form-group -->
                      </div>
                      <!-- /.col -->
                      
                      <!--<div class="card border-info mb-3 col-12">
                          <div class="card-header bg-color-0202022e font-weight-bold">Detalle</div>

                          <div class="card-body text-secondary">

                              <div class="row mb-3">
                                  <div class="col-10">
                                      <p class="card-text">Ingresa los tipos de documentos que se necesitan para este tipo de estándar.</p>
                                  </div>
                                  <div class="col-2 text-right">
                                      <button type="button" class="btn btn-primary" id="agregar_fila">
                                          <i class="ti ti-plus"></i>
                                      </button>
                                  </div>
                              </div>

                              <div class="row">
                                  <table class="table table-bordered" id="tabla_documentos">
                                      <thead  style="background-color: aliceblue;" >
                                          <tr>
                                              <th class="text-center">#</th>
                                              <th>Nombre del Documento</th>
                                              <th class="text-center"><i class="ti ti-help"></i></th>
                                          </tr>
                                      </thead>

                                      <tbody>
                                          
                                      </tbody>
                                  </table>
                              </div>

                          </div>
                      </div>-->

                      <!-- barprogress -->
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px;">
                        <div class="progress" id="barra_progress_tipoestandar_div">
                          <div id="barra_progress_tipoestandar" class="progress-bar" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
                            0%
                          </div>
                        </div>
                      </div> 

                    </div>

                    <div class="row" id="cargando-2-formulario" style="display: none;">
                      <div class="col-lg-12 text-center">
                        <i class="fas fa-spinner fa-pulse fa-3x"></i><br />
                        <br />
                        <h4>Cargando...</h4>
                      </div>
                    </div>
                    
                    <!-- /.card-body -->
                    <button type="submit" style="display: none;" id="submit-form-proveedor">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button" class="btn btn-outline-secondary  limpiar_form_tipoestandar" data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" class="btn btn-primary" id="guardar_registro_tipoestandar" ><i class="ti ti-device-floppy"></i> Guardar</button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
          <!-- /.modal -->


          
          <div class="modal fade" id="modal-agregar-docs">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2 bg-color-principal">
                  <h4 class="modal-title text-white">Documento</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-docs" name="form-agregar-docs" method="POST">
                    @csrf
                    <div class="row" id="cargando-3-formulario">
                      <!-- tipo estandar -->
                      <input type="hidden" name="iddocumento_tipo_estandar" id="iddocumento_tipo_estandar" /> 

                      <!-- descripcion -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                          <label for="descripcion_docs">Descripción <sup class="text-danger">*</sup></label>
                          <textarea name="descripcion_docs" id="descripcion_docs" class="form-control" rows="1"></textarea>
                        </div>
                      </div>    

                      <div class="col-12">
                        <div class="form-group">
                          <label for="descripcion">Tipo </label>                          
                          <select name="tipo_plantilla" id="tipo_plantilla" class="form-control form-control-sm is-valid select2" placeholder="Tipo" aria-invalid="false">
                            <option value="Estandar">Estandar</option>
                            <option value="Interno">Interno</option>
                            <option value="Modelo">Modelo</option>
                          </select>
                        </div>
                      </div>                    

                      <!-- Pdf 1 -->
                      <div class="col-12 mt-2">
                        <!-- linea divisoria -->
                        <div class="col-lg-12 borde-arriba-naranja mt-2"></div>
                        <div class="row">
                          <div class="col-md-12 p-t-15px p-b-5px" >
                            <label for="Presupuesto" class="control-label">Archivo</label>
                          </div>
                          <div class="col-6 col-md-6 col-lg-6 col-xl-6 text-center">
                            <button type="button" class="btn btn-success btn-block btn-xs" id="doc1_i"><i class="fas fa-file-upload"></i> Subir.</button>
                            <input type="hidden" id="doc_old_1" name="doc_old_1" />
                            <input style="display: none;" id="doc1" type="file" name="doc1" accept=".pdf, .docx, .doc" class="docpdf" />
                          </div>
                          <div class="col-6 col-md-6 col-lg-6 col-xl-6 text-center">
                            <button type="button" class="btn btn-info btn-block btn-xs" onclick="re_visualizacion(1, '', '');"><i class="fa fa-eye"></i> PDF.</button>
                          </div>
                        </div>
                        <div id="doc1_ver" class="text-center mt-4">
                          <img src="/assets/images/default/word_pdf.png" alt="" width="50%" />
                        </div>
                        <div class="text-center" id="doc1_nombre"><!-- aqui va el nombre del pdf --></div>
                      </div>
                      
                      <!-- barprogress -->
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px;">
                        <div class="progress" id="barra_progress_docs_div">
                          <div id="barra_progress_docs" class="progress-bar" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
                            0%
                          </div>
                        </div>
                      </div> 

                    </div>

                    <div class="row" id="cargando-4-formulario" style="display: none;">
                      <div class="col-lg-12 text-center">
                        <i class="fas fa-spinner fa-pulse fa-3x"></i><br />
                        <br />
                        <h4>Cargando...</h4>
                      </div>
                    </div>
                    
                    <!-- /.card-body -->
                    <button type="submit" style="display: none;" id="submit-form-docs">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button" class="btn btn-outline-secondary limpiar_form_docs" data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" class="btn btn-primary" id="guardar_registro_docs" ><i class="ti ti-device-floppy"></i> Guardar</button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
          <!-- /.modal -->



        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->
    @else
      @include('componentes_erp.no-permiso')
    @endif

    @include('layouts.lte_footer')  

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->

  @include('layouts.lte_script')  


  <script src="{{ asset('assets/js/tipo_estandar.js') }}?version_erp=01.06"></script>
  <script src="{{ asset('assets/js/documentotipo_estandar.js') }}?version_erp=01.04"></script>



  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
