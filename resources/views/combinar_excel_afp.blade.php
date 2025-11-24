<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">

  <title>Combinar Planilla AFP | ERP Optimiza 360</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/ico-opt.png') }}" type="image/png">

  @include('layouts.lte_head')
  <link rel="stylesheet" href="{{ asset('assets/jstree-3.3.17/dist/themes/default/style.min.css') }}" />

  <style>
    #tabla-proyectos_filter { width: calc(100% - 10px) !important; display: flex !important; justify-content: space-between !important; }
    #tabla-proyectos_filter label { width: 100% !important;  }
    #tabla-proyectos_filter label input { width: 100% !important;   }

    /* Indicadores de orden simple (opcional) */
    th.sortable { cursor:pointer; position:relative; }
    th.sortable.asc::after  { content:"▲"; font-size:.7rem; position:absolute; right:.4rem; }
    th.sortable.desc::after { content:"▼"; font-size:.7rem; position:absolute; right:.4rem; }

    .fila-proyecto.selected {  background-color: #e7f1ff !important; }
    .fila-proyecto-presupuesto.selected {  background-color: #e7f1ff !important; }
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
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-p-descargar"><i class="ti ti-download"></i> Descargar</a></li>
            <li class="nav-item"><a href="#" class="nav-link py-1" id="opcion-p-eliminar"><i class="ti ti-trash-x"></i> Eliminar Permanente</a></li>
            
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

    @if (auth()->user()->perm_importar_hora)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0">Combinar Planilla AFP </h1>
              </div><!-- /.col -->
              <div class="col-sm-6">
                <div class="float-right">

                  <div class="btn-group btn-agregar-proyecto">
                    <button type="button" class="btn btn-success" style="border-color: #1a6b2c !important;" data-toggle="modal" data-target="#modal-combinar-txt" onclick="limpiar_form_combinar();" ><i class="ti ti-file-excel"></i> Crear nuevo</button>
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" style="border-color: #1a6b2c !important;">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      {{-- <a class="dropdown-item" href="#"><i class="ti ti-user-up"></i> Carga Masiva</a> --}}
                      <div class="dropdown-divider my-0"></div>
                      {{-- <a class="dropdown-item" href="#"><i class="ti ti-user-x"></i> Baja masiva</a>                     --}}
                    </div>
                  </div>                

                </div>

                
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">

              
              <!-- ./col -->
              <div class="col" id="div-tabla-principal-proyecto">
                <div class="col-12 col-sm-12">
                  <div class="card card-primary card-outline card-outline-tabs">
                    <div class="card-header p-0 border-bottom-0">
                      <ul class="nav nav-tabs" id="custom-tabs-tab" role="tablist">
                        <li class="nav-item">
                          <a class="nav-link active" id="custom-tabs-resumen-tab" data-toggle="pill" href="#custom-tabs-resumen" role="tab" aria-controls="custom-tabs-resumen" aria-selected="true">Resumen</a>
                        </li>
                        <li class="nav-item">
                          <a class="nav-link" id="custom-tabs-profile-tab" data-toggle="pill" href="#custom-tabs-detallado" role="tab" aria-controls="custom-tabs-detallado" aria-selected="false"> Detallado</a>
                        </li>
                        
                      </ul>
                    </div>
                    <div class="card-body">
                      <div class="tab-content" id="custom-tabs-tabContent">
                        <div class="tab-pane fade show active" id="custom-tabs-resumen" role="tabpanel" aria-labelledby="custom-tabs-resumen-tab">
                          <div  id="mostrar-resumen-analisis" >                          
                          
                          </div>

                           <!-- Botón para descargar el archivo combinado -->
                          <button onclick="descargar_combinado()" class="btn btn-success"><i class="ti ti-download"></i> Descargar Excel</button>
                          {{-- <button onclick="descargar_combinado('excel')" class="btn btn-success">Descargar Excel</button> --}}

                          <div  id="mostrar-lista-combinada" >                          
                          
                          </div>

                         
                                                    
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-detallado" role="tabpanel" aria-labelledby="custom-tabs-detallado-tab">
                         {{-- Detalle --}}
                        
                         
                        </div>                        
                      </div>
                    </div>
                    <!-- /.card -->
                  </div>
                </div>

                
              </div>
              <div class="col-lg-12" id="div-ver-detalle-proyecto" style="display: none;">
                
              </div>
            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->


          <div class="modal fade" id="modal-combinar-txt">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Combinar EXCEL</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-combinar-txt" name="form-combinar-txt" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">  

                      <!--  -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                          <label for="archivo_excel">Archivo </label>                              
                          <button type="button" id="btn-excel-eliminar" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash-x"></i> Eliminar</button>
                          <input type="file" name="archivo_excel[]" id="archivo_excel" class="form-control" multiple  required>                    
                        </div>
                        <div id="lista-archivos-seleccionados"></div>
                      </div>                     

                      <div class="col-lg-12 col-md-12">
                        <div class="mt-3" id="resultado"></div>
                      </div>                    

                      
                      <!-- barprogress -->
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px;">
                        <div class="progress" id="barra_progress_proyecto_div">
                          <div id="barra_progress_proyecto" class="progress-bar" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
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
                    <button type="submit" style="display: none;" id="submit-form-combinar-txt">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button"  class="btn btn-outline-danger " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" id="guardar_combinar_txt" class="btn btn-success" ><i class="ti ti-device-floppy"></i> Cargar</button>
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

  <script src="{{ asset('assets/jstree-3.3.17/dist/jstree.min.js') }}"></script>
  <script src="{{ asset('assets/js/combinar_excel_afp.js') }}?version_erp=01.02"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
