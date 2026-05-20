<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Área Personal | Portal Proveedores D&C</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" type="image/png">

  @include('layouts.lte_head')
  <!--<link rel="stylesheet" href="{{ asset('assets/jstree-3.3.17/dist/themes/default/style.min.css') }}" />-->

  <style>
    #tabla-bancos_filter { width: calc(100% - 10px) !important; display: flex !important; justify-content: space-between !important; }
    #tabla-bancos_filter label { width: 100% !important;  }
    #tabla-bancos_filter label input { width: 100% !important;   }

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

    @if (auth()->user()->perm_bancos)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-3">
                <h1 class="m-0">Área Personal</h1>
              </div><!-- /.col -->
              <div class="col-3">
                <div class="float-right">

                  <div class="btn-group btn-agregar-area_p">
                    <button type="button" class="btn btn-primary" style="border-color: #2e6da4 !important;" data-toggle="modal" data-target="#modal-agregar-area_p" onclick="limpiar_form_area_p();" ><i class="fas fa-plus-circle"></i> Crear nuevo</button>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="border-color: #2e6da4 !important;">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      <a class="dropdown-item" href="#"><i class="ti ti-user-up"></i> Carga Masiva</a>
                      <div class="dropdown-divider my-0"></div>
                      <a class="dropdown-item" href="#"><i class="ti ti-user-x"></i> Baja masiva</a>                    
                    </div>
                  </div>

                  <button type="button" class="btn btn-danger btn-cancelar m-r-10px" onclick="show_hide_escenario(1);" style="display: none;"><i class="ri-arrow-left-line"></i> Regresar</button>

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
              <div class="col-6" id="div-tabla-principal-area_p">
                <div class="card">
                  
                  <div class="card-body pb-1">
                    <div class="row mb-2">                    
                      <div class="col">
                        <input type="search" id="buscar" class="form-control form-control-sm" placeholder="Buscar...">
                      </div>
                      <div class="col-auto">
                        <select id="perPage" class="form-select form-select-sm">
                          <option value="5">5</option>
                          <option value="10" selected>10</option>
                          <option value="25" >25</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                        </select>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-info recargar-tabla-area-persona" data-toggle="tooltip" data-original-title="Recargar tabla" ><i class="ti ti-refresh"></i></button>
                      </div>
                    </div>
                    
                    <div class="table-responsive">
                    
                      <table class="table table-bordered table-hover styletabla" id="tabla-area-persona">
                        <thead>
                          <tr>                        
                            <th>Acciones</th>
                            <th data-sort="codigo" class="sortable">Código</th>
                            <th data-sort="descripcion" class="sortable">Descripcion</th>
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
                    <ul class="pagination pagination-sm m-0 float-right" id="paginacion">
                      
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->


          <div class="modal fade" id="modal-agregar-area_p">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Área Personal</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-area_p" name="form-agregar-area_p" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">
                      <!-- tipo estandar -->
                      <input type="hidden" name="idarea_persona" id="idarea_persona" /> 

                      <!-- descripcion -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                          <label for="descripcion">Descripción <sup class="text-danger">*</sup></label>
                          <input type="text" name="descripcion" class="form-control form-control-sm" id="descripcion"  />
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
                    <button type="submit" style="display: none;" id="submit-form-area_p">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button" class="btn btn-outline-danger " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" class="btn btn-success" id="guardar_registro_area_p" ><i class="ti ti-device-floppy"></i> Guardar</button>
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


  <script src="{{ asset('assets/js/area_persona.js') }}?version_erp=01.15"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
