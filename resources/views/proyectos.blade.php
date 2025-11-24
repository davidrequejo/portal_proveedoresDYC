<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Proyecto | ERP Optimiza 360</title>

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

    @if (auth()->user()->perm_recurso)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0">Proyectos</h1>
              </div><!-- /.col -->
              <div class="col-sm-6">
                <div class="float-right">

                  <div class="btn-group btn-agregar-proyecto">
                    <button type="button" class="btn btn-success" style="border-color: #1a6b2c !important;" data-toggle="modal" data-target="#modal-agregar-proyecto" onclick="limpiar_form_proyecto();" ><i class="ti ti-users-plus"></i> Crear nuevo</button>
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" style="border-color: #1a6b2c !important;">
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
              <div class="col" id="div-tabla-principal-proyecto">
                <div class="card">
                  
                  <div class="card-body pb-1">
                    <div class="row mb-2">                    
                      <div class="col">
                        <input type="search" id="buscar" class="form-control form-control-sm" placeholder="Buscar proyecto...">
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
                        <button type="button" class="btn btn-sm btn-outline-info recargar-tabla-proyecto" data-toggle="tooltip" data-original-title="Recargar tabla" ><i class="ti ti-refresh"></i></button>
                      </div>
                    </div>
                    
                    <div class="table-responsive">
                    
                      <table class="table table-bordered table-hover" id="tabla-proyectos">
                        <thead>
                          <tr>                        
                            <th>Acciones</th>
                            <th data-sort="codigo"      class="sortable">Código</th>
                            <th data-sort="descripcion" class="sortable">Descripción</th>
                            <th data-sort="cliente"     class="sortable">Cliente</th>
                            <th data-sort="empresa"     class="sortable">Empresa</th>
                            <th data-sort="fecha_inicio"class="sortable">F. Inicio</th>
                            <th data-sort="fecha_fin"   class="sortable">F. Fin</th>
                            <th data-sort="total_presupuesto" class="sortable">Presupuesto</th>
                            <th data-sort="direccion" class="sortable">direccion</th>
                            <th data-sort="ubicacion" class="sortable">ubicacion</th>
                            
                          </tr>
                        </thead>
                        <tbody>                     
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right" id="paginacion">
                      
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-lg-12" id="div-ver-detalle-proyecto" style="display: none;">
                
              </div>
            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->


          <div class="modal fade" id="modal-agregar-proyecto">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Proyecto</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-proyecto" name="form-agregar-proyecto" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">
                      <!-- id proyecto -->
                      <input type="hidden" name="idproyecto" id="idproyecto" />

                      <!-- Tipo de documento -->
                      <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                        <div class="form-group">
                          <label for="codigo">Cod proyecto</label>
                          <input type="text" name="codigo" class="form-control" id="codigo" placeholder="ejmpl. 200343" />
                        </div>
                      </div>

                      <!-- Nombre -->
                      <div class="col-12 col-sm-6 col-md-8 col-lg-8">
                        <div class="form-group">
                          <label for="descripcion">Descripción</label>                          
                          <textarea class="form-control" name="descripcion" id="descripcion" cols="30" rows="1" placeholder="ejmpl. Los Jardines"></textarea>
                        </div>
                      </div>
                      <!-- Direccion -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="direccion">Dirección</label>
                          <textarea class="form-control" name="direccion" id="direccion" cols="30" rows="2" placeholder="ejmpl. Jiron. las flores cdr 3"></textarea>
                        </div>
                      </div> 
                      <!-- Direccion -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="ubicacion">Ubicacion</label>
                          <textarea class="form-control" name="ubicacion" id="ubicacion" cols="30" rows="2" placeholder="ejmpl. Lima lima Peru"></textarea>
                        </div>
                      </div> 

                      <!-- Fecha Inicio -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="fecha_inicio">Fecha inicio</label>
                          <input type="date" name="fecha_inicio" class="form-control" id="fecha_inicio"  />
                        </div>
                      </div> 
                      <!-- Fecha fin -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="fecha_fin">Fecha fin</label>
                          <input type="date" name="fecha_fin" class="form-control" id="fecha_fin"  />
                        </div>
                      </div>                                          

                      <!-- Empresa -->
                      <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="idempresa">Empresa</label>
                          <select name="idempresa" id="idempresa" class="form-control select2" style="width: 100%;"> </select>                          
                        </div>
                      </div>

                      <!-- Socio Negocio -->
                      <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="idsocio_negocio">Socio Negocio</label>
                          <select name="idsocio_negocio" id="idsocio_negocio" class="form-control select2" style="width: 100%;"> </select>                          
                        </div>
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
                    <button type="submit" style="display: none;" id="submit-form-proyecto">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button" class="btn btn-outline-danger " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" class="btn btn-success" id="guardar_registro_proyecto" ><i class="ti ti-device-floppy"></i> Guardar</button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
          <!-- /.modal -->

          <div class="modal fade" id="modal-agregar-presupuesto">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Presupuestos</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="table-responsive">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <th style="width: 10px">#</th>
                          <th>Task</th>
                          <th>Progress</th>
                          <th style="width: 40px">Label</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>1.</td>
                          <td>Update software</td>
                          <td>
                            <div class="progress progress-xs">
                              <div class="progress-bar progress-bar-danger" style="width: 55%"></div>
                            </div>
                          </td>
                          <td><span class="badge bg-danger">55%</span></td>
                        </tr>
                        <tr>
                          <td>2.</td>
                          <td>Clean database</td>
                          <td>
                            <div class="progress progress-xs">
                              <div class="progress-bar bg-warning" style="width: 70%"></div>
                            </div>
                          </td>
                          <td><span class="badge bg-warning">70%</span></td>
                        </tr>
                        <tr>
                          <td>3.</td>
                          <td>Cron job running</td>
                          <td>
                            <div class="progress progress-xs progress-striped active">
                              <div class="progress-bar bg-primary" style="width: 30%"></div>
                            </div>
                          </td>
                          <td><span class="badge bg-primary">30%</span></td>
                        </tr>
                        <tr>
                          <td>4.</td>
                          <td>Fix and squish bugs</td>
                          <td>
                            <div class="progress progress-xs progress-striped active">
                              <div class="progress-bar bg-success" style="width: 90%"></div>
                            </div>
                          </td>
                          <td><span class="badge bg-success">90%</span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
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
  <script src="{{ asset('assets/js/proyecto.js') }}?version_erp=01.02"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
