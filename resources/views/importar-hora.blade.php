<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">

  <title>Importar Horas | ERP Optimiza 360</title>

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
                <h1 class="m-0">Importar Horas </h1>
              </div><!-- /.col -->
              <div class="col-sm-6">
                <div class="float-right">

                  <div class="btn-group btn-agregar-proyecto">
                    <button type="button" class="btn btn-success" style="border-color: #1a6b2c !important;" data-toggle="modal" data-target="#modal-importar-horas" onclick="limpiar_form_hora();" ><i class="ti ti-file-excel"></i> Crear nuevo</button>
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
                          <div class="row mb-2">                    
                            <div class="col">
                              <input type="search" id="buscar" class="form-control form-control-sm" placeholder="Buscar...">
                            </div>
                            <div class="col-auto">
                              <select id="per_page" class="form-select form-select-sm">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25" >25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                              </select>
                            </div>
                            <div class="col-auto">
                              <button type="button" class="btn btn-sm btn-outline-info recargar-tabla-hora" data-toggle="tooltip" data-original-title="Recargar tabla" ><i class="ti ti-refresh"></i></button>
                            </div>
                          </div>
                        
                          <div class="table-responsive">
                          
                            <table class="table table-bordered table-hover" id="tabla-cabeceras">
                              <thead>
                                <tr>                        
                                  <th>Acciones</th>
                                  <th data-sort="codigo"      class="sortable">ID</th>
                                  <th data-sort="descripcion" class="sortable text-nowrap" >Archivo <span style="color:transparent" >--------------------------------------------------------------</span> </th>
                                  <th data-sort="cliente"     class="sortable">Hoja</th>
                                  <th data-sort="empresa"     class="sortable">Filas</th>
                                  <th data-sort="fecha_inicio"class="sortable">Tamaño</th>

                                  <th class="py-2">Lun hn</th>
                                  <th class="py-2">Lun he</th>

                                  <th class="py-2">Mar hn</th>
                                  <th class="py-2">Mar he</th>

                                  <th class="py-2">Mie hn</th>
                                  <th class="py-2">Mie he</th>

                                  <th class="py-2">Jue hn</th>
                                  <th class="py-2">Jue he</th>

                                  <th class="py-2">Vie hn</th>
                                  <th class="py-2">Vie he</th>

                                  <th class="py-2">Sab hn</th>
                                  <th class="py-2">Sab he</th>

                                  <th class="py-2">Dom hn</th>
                                  <th class="py-2">Dom he</th>

                                  <th class="py-2">HN</th>
                                  <th class="py-2">HE</th>

                                  <th data-sort="fecha_fin"   class="sortable">Creado</th>
                                  
                                </tr>
                              </thead>
                              <tbody>                     
                              </tbody>
                            </table>
                          </div>
                          <div class="card-footer clearfix">
                            <ul class="pagination pagination-sm m-0 float-right" id="paginacion">   </ul>
                          </div>
                          
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-detallado" role="tabpanel" aria-labelledby="custom-tabs-detallado-tab">
                          <div class="row mb-2">                    
                            <div class="col-lg-10">
                              <div class="row">
                                <div class="col-lg-1 col-xl-1">
                                  <div class="text-center">
                                    <button type="button" class="btn btn-sm btn-block btn-primary" id="btn-buscar-detalle"><i class="ti ti-search"></i></button>
                                  </div>
                                </div>
                                <div class="col-lg-2 col-xl-2">
                                  <div class="form-group">
                                    <select id="filtro_color" class="form-control form-control-sm">
                                      <option value="">Todos color</option>
                                      <option value="Verde">Verde</option>
                                      <option value="Amarillo">Amarillo</option>
                                      <option value="Naranja">Naranja</option>
                                      <option value="Blanco">Blanco</option>                                      
                                      <option value="Negro">Negro</option>                                      
                                      <option value="Rojo">Rojo</option>
                                      <option value="Rosa">Rosa</option>
                                      <option value="Morado">Morado</option>
                                      <option value="Azul">Azul</option>
                                      <option value="Celeste">Celeste</option>                                      
                                      <option value="Marrón">Marrón</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="col-lg-2 col-xl-2">
                                  <div class="form-group">
                                    <select id="columna_detalle" class="form-control form-control-sm">
                                      <option value="dni">DNI</option>
                                      <option value="apellidos_nombres">NOMBRE APELLIDOS</option>
                                      <option value="nombre_archivo">ARCHIVO</option>
                                      <option value="observaciones">OBSERVACIÓN</option>
                                      <option value="idregistro_horas" >ID</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="col-lg-7 col-xl-7">
                                  <input type="search_detalle" id="buscar_detalle" class="form-control form-control-sm" placeholder="Buscar...">
                                </div>
                              </div>
                              
                            </div>
                            <!-- /.col -->
                            <div class="col-auto">
                              <select id="per_page_Detalle" class="form-select form-select-sm">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25" >25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                              </select>
                            </div>
                            <div class="col-auto">
                              <button type="button" class="btn btn-sm btn-outline-info recargar-tabla-detalle" data-toggle="tooltip" data-original-title="Recargar tabla" ><i class="ti ti-refresh"></i></button>
                              <button type="button" class="btn btn-sm btn-outline-success exportar-tabla-detalle" data-toggle="tooltip" data-original-title="Exportar Excel" ><i class="ti ti-file-excel"></i></button>

                            </div>
                          </div>
                          <!-- /.row -->
                        
                          <div class="table-responsive">
                          
                            <table class="table table-bordered table-hover" id="tabla-detalle">
                              <thead>
                                <tr>                        
                                  <th class="text-center" ><i class="ti ti-settings-bolt fs-12 "></i></th>
                                  <th class="py-2">N°</th>
                                  <th class="py-2">Nombres y apellidos <div class="bg-light" style="overflow: auto; resize: horizontal; height: 10px; width: 200px;"> </div></th>
                                  <th class="py-2">dni</th>

                                  <th class="py-2">Lun hn</th>
                                  <th class="py-2">Lun he</th>

                                  <th class="py-2">Mar hn</th>
                                  <th class="py-2">Mar he</th>

                                  <th class="py-2">Mie hn</th>
                                  <th class="py-2">Mie he</th>

                                  <th class="py-2">Jue hn</th>
                                  <th class="py-2">Jue he</th>

                                  <th class="py-2">Vie hn</th>
                                  <th class="py-2">Vie he</th>

                                  <th class="py-2">Sab hn</th>
                                  <th class="py-2">Sab he</th>

                                  <th class="py-2">Dom hn</th>
                                  <th class="py-2">Dom he</th>

                                  <th class="py-2">Observación</th>
                                  <th class="py-2">Archivo</th>
                                  
                                </tr>
                              </thead>
                              <tbody>                     
                              </tbody>
                            </table>
                          </div>
                          <div class="card-footer clearfix">
                            <ul class="pagination pagination-sm m-0 float-right" id="paginacion_detalle">   </ul>
                          </div>
                          <!-- /.card-footer -->
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


          <div class="modal fade" id="modal-importar-horas">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Importar Horas</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-importar-horas" name="form-importar-horas" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">
                      <!-- id proyecto -->
                      <input type="hidden" name="idproyecto" id="idproyecto" />

                      

                      <!-- Nombre -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                          <label for="nombre_proyecto">Proyecto</label>                          
                          <textarea class="form-control" name="nombre_proyecto" id="nombre_proyecto" cols="30" rows="1" placeholder="ejmpl. 06006 CBPO-REHABILITACIÓN RED DE AGUA POTABLE">06006 CBPO-REHABILITACIÓN RED DE AGUA POTABLE, ESTACIÓN DE BOMBEO DE AGUA RESIDUALES Y COLECTORAS DEL DISTRITO DE CATACAOS, PROVINCIA DE PIURA, DEPARTAMENTO DE PIURA - SISTEMA DE AGUA POTABLE Y ALCANTARILLADO ETAPA 1 - CUI N° 2536439”</textarea>
                        </div>
                      </div>
                      <!-- Direccion -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="partida_control">Partida Control</label>
                          <textarea class="form-control" name="partida_control" id="partida_control" cols="30" rows="1" placeholder="ejmpl. 0101010  SECTOR 3"></textarea>
                        </div>
                      </div> 
                      <!-- Direccion -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="concepto">Concepto</label>
                          <textarea class="form-control" name="concepto" id="concepto" cols="30" rows="1" placeholder="ejmpl. 10005017 HORAS LABORALES">09012001 HRS LABORABLES</textarea>
                        </div>
                      </div> 

                      <!-- Hoja de excel -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                          <label for="archivo_excel">Archivo Excel (.xlsx / .xls)</label>    
                          <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#modal-previsualizar-excel"><i class="ti ti-eye-share"></i> Ver</button>
                          <button type="button" id="btn-excel-eliminar" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash-x"></i> Eliminar</button>

                          <input type="file" name="archivo_excel" id="archivo_excel" class="form-control"  required>                    
                        </div>
                      </div>

                      <!-- Hoja de excel -->
                      <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="sheet_index">Hoja de excel</label>
                          <select name="sheet_index" id="sheet_index" class="form-control select2" style="width: 100%;"> </select>                          
                        </div>
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
                    <button type="submit" style="display: none;" id="submit-form-proyecto">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button"  class="btn btn-outline-danger " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" id="guardar_registro_horas" class="btn btn-success" ><i class="ti ti-device-floppy"></i> Importar</button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
          <!-- /.modal -->

          <div class="modal fade" id="modal-previsualizar-excel">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Previsualizacion de Excel <small>(mostrando los primeros registros)</small> </h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="table-responsive " id="preview-wrap" style="display:none;">
                          <table class="table table-sm table-bordered" id="preview-table">
                            <thead></thead>
                            <tbody></tbody>
                          </table>
                        </div>
                      </div>
                  </div>
                </div>              
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
          <!-- /.modal -->

          <div class="modal fade" id="modal-descargar-archivo">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Rellenar plantilla </h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-descargar-excel" name="form-descargar-excel" enctype="multipart/form-data" method="POST">
                    @csrf

                    <input type="hidden" name="idregistro_horas" id="idregistro_horas" class="form-control" > 
                    <!-- Opcionales (con defaults) -->
                    <input type="hidden" name="sheet_name" value="TAREO">
                    <input type="hidden" name="row_start"  value="8">
                    <input type="hidden" name="dni_col"    value="C">

                    <div class="row">
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="mb-2">
                          <label>Archivo base</label> <br>
                          <span id="span-archivo-base" ></span>
                        </div>
                      </div>
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group mb-2">
                          <label>Plantilla Destino <small>(.xlsx / .xlsm / .xls )</small> </label>
                          <input type="file" name="file_excel_plantilla" id="file_excel_plantilla" class="form-control"  >
                        </div>
                      </div>

                      <div class="col-12 ">
                        <div class="form-group form-check " style="margin-bottom: 0 !important;">
                          <input type="checkbox" class="form-check-input" id="rellenar_solo_celdas_vacias" name="rellenar_solo_celdas_vacias" value="1" checked>
                          <label class="form-check-label" for="rellenar_solo_celdas_vacias">
                            Rellenar solo celdas vacías
                          </label>
                        </div>
                      </div>

                      <div class="col-12">
                        <div class="form-group form-check mb-3" style="margin-bottom: 0 !important;">
                          <input type="checkbox" class="form-check-input" id="no_tarear_verde" name="no_tarear_verde" value="1" checked>
                          <label class="form-check-label" for="no_tarear_verde">
                            NO Tarear descanzo médico(color verde).
                          </label>
                        </div>
                      </div>

                    </div> 
                    <button type="submit" style="display: none;" id="submit-form-descargar-excel-plantilla">Submit</button>
                  </form>
                  
                </div>   
                <div class="modal-footer justify-content-between py-1">
                  <button type="button"  class="btn btn-outline-danger " data-dismiss="modal">Cerrar</button>
                  <button type="button" id="descargar_excel_plantilla" class="btn btn-success" ><i class="ti ti-download"></i> Descargar</button>
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
  <script src="{{ asset('assets/js/importar_hora_oberti.js') }}?version_erp=01.02"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
