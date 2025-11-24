<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">

  <title>AdminLTE 3 | Presupuestos</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/ico-opt.png') }}" type="image/png">

  @include('layouts.lte_head')
  <link rel="stylesheet" href="{{ asset('assets/jstree-3.3.17/dist/themes/default/style.min.css') }}" />

</head>
<body class="hold-transition sidebar-mini layout-footer-fixed sidebar-collapse layout-fixed">
  <div class="wrapper">

    <!-- Preloader -->
    @include('layouts.lte_preloader')

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
                <h1 class="m-0">Presupuesto</h1>
              </div><!-- /.col -->
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active">Presupuesto</li>
                </ol>
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

              {{-- Arbol de Proyectos --}}
              <div class="col-auto" style="width:300px;">
                <div class="row">
                  <div class="col-lg-12 pb-2">
                    <input type="text" class="form-control form-control-sm" name="" id="buscador-arbol" placeholder="Buscar...">
                  </div>
                  <div class="col-lg-12">
                    <div id="arbol-proyecto">
                  
                    </div>
                  </div>
                </div>
                
              </div>
              <!-- ./col -->
              <div class="col">
                <div class="row">
                  <div class="col-12 col-sm-12" id="div-card-presupuesto" style="display: none;">
                    <div class="card card-primary card-outline card-outline-tabs">
                      <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="tabs-p-tab" role="tablist">
                          <li class="nav-item">
                            <a class="nav-link active" id="tabs-p-presupuesto-tab" data-toggle="pill" href="#tabs-p-presupuesto" role="tab" aria-controls="tabs-p-presupuesto" aria-selected="true">Presupuesto</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" id="tabs-p-cronograma-tab" data-toggle="pill" href="#tabs-p-cronograma" role="tab" aria-controls="tabs-p-cronograma" aria-selected="false">Cronograma</a>
                          </li>
                          
                        </ul>
                      </div>
                      <div class="card-body">
                        <div class="tab-content" id="tabs-p-tabContent">
                          <div class="tab-pane fade show active" id="tabs-p-presupuesto" role="tabpanel" aria-labelledby="tabs-p-presupuesto-tab">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin malesuada lacus ullamcorper dui molestie, sit amet congue quam finibus. Etiam ultricies nunc non magna feugiat commodo. Etiam odio magna, mollis auctor felis vitae, ullamcorper ornare ligula. Proin pellentesque tincidunt nisi, vitae ullamcorper felis aliquam id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin id orci eu lectus blandit suscipit. Phasellus porta, ante et varius ornare, sem enim sollicitudin eros, at commodo leo est vitae lacus. Etiam ut porta sem. Proin porttitor porta nisl, id tempor risus rhoncus quis. In in quam a nibh cursus pulvinar non consequat neque. Mauris lacus elit, condimentum ac condimentum at, semper vitae lectus. Cras lacinia erat eget sapien porta consectetur.
                          </div>
                          <div class="tab-pane fade" id="tabs-p-cronograma" role="tabpanel" aria-labelledby="tabs-p-cronograma-tab">
                            Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc euismod pellentesque diam.
                          </div>                        
                        </div>
                      </div>
                      <!-- /.card -->
                    </div>
                  </div>
                  
                  <div class="col-12 col-sm-12" id="div-card-bienvenido">                
                    <div class="card">
                      <div class="card-header">
                        <h3 class="card-title">Bienvenido!!</h3>
                      </div>
                      <!-- /.card-header -->
                      <div class="card-body">
                        <div id="div-botones-presupuesto" style="display: none;">
                          <div class="row">
                            <div class="col-lg-4">
                              <h6>Importacion de presupuesto</h6>
                              <button type="button" class="btn btn-sm bg-gradient-primary btn-lg" data-toggle="modal" data-target="#modal-agregar-presupuesto">Crear presupuesto</button>                              
                              <button type="button" class="btn btn-sm bg-gradient-primary btn-lg" data-toggle="modal" data-target="#">Importar presupuesto</button>                              
                            </div>
                            <div class="col-lg-8">
                              <h6>Importacion de Cronograma</h6>
                              <button type="button" class="btn btn-sm bg-gradient-info pb-2" data-toggle="modal" data-target="#">Importar v1</button>
                              <button type="button" class="btn btn-sm bg-gradient-info pb-2" data-toggle="modal" data-target="#">Importar v2</button>
                              <button type="button" class="btn btn-sm bg-gradient-info pb-2" data-toggle="modal" data-target="#">Importar v3</button>
                            </div>
                          </div>
                           
                        </div>
                        <div class="alert alert-warning alert-dismissible fade show alerta-bienvenido" role="alert" >                          
                          <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
                          Por favor, seleccione una opción del árbol para ver los detalles y modificarlo según corresponda.
                        </div>
                      </div>

                      <!-- /.card-body -->
                      {{-- <div class="card-footer clearfix">
                        <ul class="pagination pagination-sm m-0 float-right">
                          <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                          <li class="page-item"><a class="page-link" href="#">1</a></li>
                          <li class="page-item"><a class="page-link" href="#">2</a></li>
                          <li class="page-item"><a class="page-link" href="#">3</a></li>
                          <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                        </ul>
                      </div> --}}
                    </div>
                  </div>

                </div>
                <!-- /.row -->
              </div>
              <!-- /.col -->
            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->

          <div class="modal fade" id="modal-agregar-grupo">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2">
                  <h4 class="modal-title">Crear Grupo</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-grupo" name="form-agregar-grupo" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">
                      <!-- id proyecto -->
                      <input type="hidden" name="idpresupuesto_grupo" id="pg_idpresupuesto_grupo" />                      

                      <!-- Descripción -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                          <label for="pg_descripcion">Descripción</label>                          
                          <textarea class="form-control" name="descripcion" id="pg_descripcion" cols="30" rows="1" placeholder="ejmpl. PAPELERA"></textarea>
                        </div>
                      </div>
                      <!-- Icono -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="pg_icono">Icono</label>
                          <select class="form-control" name="icono" id="pg_icono">
                            <option value="ti ti-folders" title="ti ti-folders">Folder 1</option>
                            <option value="ti ti-folder-x" title="ti ti-folder-x">Folder 2</option>
                            <option value="ti ti-file" title="ti ti-file">Archivo 1</option>
                            <option value="ti ti-clipboard-text" title="ti ti-clipboard-text"> Archivo 2</option>
                            <option value="ti ti-buildings" title="ti ti-buildings">Edificio 1</option>
                            <option value="ti ti-building" title="ti ti-building">Edificio 2</option>
                            <option value="ti ti-building-community" title="ti ti-building-community">Edificio 3</option>
                            <option value="ti ti-briefcase" title="ti ti-briefcase">Maleta</option> 
                          </select>
                        </div>
                      </div> 
                      <!-- Color -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="pg_icono_color">Color</label>
                          <select class="form-control" name="icono_color" id="pg_icono_color">
                            <option value="text-info" title="ti ti-square-rounded-filled text-info">Celeste</option>
                            <option value="text-warning" title="ti ti-square-rounded-filled text-warning">Amarillo</option>
                            <option value="text-primary" title="ti ti-square-rounded-filled text-primary">Azul</option>
                            <option value="text-success" title="ti ti-square-rounded-filled text-success">Verde</option>                            
                            <option value="text-danger" title="ti ti-square-rounded-filled text-danger">Rojo</option>
                            <option value="text-secondary" title="ti ti-square-rounded-filled text-secondary">Plomo</option>
                           
                          </select>
                        </div>
                      </div>                       

                      <div class="col-lg-12 col-md-12">
                        <div class="mt-3" id="resultado_pg_crear_grupo"></div>
                      </div>
                      
                      <!-- barprogress -->
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"id="barra_progress_pg_crear_grupo_div" style="margin-top:20px; display: none;">
                        <div class="progress" >
                          <div id="barra_progress_pg_crear_grupo" class="progress-bar" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
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
                    <button type="submit" style="display: none;" id="submit-form-pg-grupo">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button"  class="btn btn-sm btn-outline-danger " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" class="btn btn-sm btn-success" id="guardar_registro_pg_grupo"  ><i class="ti ti-device-floppy"></i> Guardar</button>
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
                  <h4 class="modal-title">Crear Presupuesto</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-presupuesto" name="form-agregar-presupuesto" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="row" id="cargando-3-formulario">
                      <!-- id proyecto -->
                      <input type="hidden" name="idpresupuesto" id="p_idpresupuesto" />                      
                      <input type="hidden" name="idpresupuesto_grupo" id="p_idpresupuesto_grupo" />                      

                      <!-- Descripción -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="form-group">
                          <label for="p_descripcion">Descripción</label>                          
                          <textarea class="form-control" name="descripcion" id="p_descripcion" cols="30" rows="2" placeholder="ejmpl. PRESUPUESTO DE CONSTRUCCION DE EDIFICIO LAS GERANIOS DEL NORTE "></textarea>
                        </div>
                      </div>
                      <!-- Resumen -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                        <div class="form-group">
                          <label for="p_descripcion_resumen">Resumen</label>                          
                          <textarea class="form-control" name="descripcion_resumen" id="p_descripcion_resumen" cols="30" rows="2" placeholder="ejmpl. CONSTRUCCION GERANIOS"></textarea>
                        </div>
                      </div>
                      <!-- Tipo -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="p_tipo">Tipo</label>
                          <select class="form-control" name="tipo" id="p_tipo">
                            <option value="META">META</option>
                            <option value="LINEA BASE">META</option>
                            <option value="ACTUAL">ACTUAL</option>
                            <option value="BANCO">BANCO</option>
                          </select>
                        </div>
                      </div> 

                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="p_idproyecto">Proyecto</label>
                          <select class="form-control" name="idproyecto" id="p_idproyecto">                            
                          </select>
                        </div>
                      </div> 

                      <div class="col-lg-12">
                        <div class="form-group">
                          <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="p-check-datos-adicionales">
                            <label class="custom-control-label" for="p-check-datos-adicionales">Datos adicionales</label>
                          </div>
                        </div>
                      </div>

                      <div class="col-lg-12" id="div-datos-adicionales" style="display:none">
                        <div class="row">
                          <!-- Icono -->
                          <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group">
                              <label for="p_icono">Icono</label>
                              <select class="form-control" name="icono" id="p_icono">
                                <option value="ti ti-buildings" title="ti ti-buildings">Edificio 1</option>
                                <option value="ti ti-building" title="ti ti-building">Edificio 2</option>
                                <option value="ti ti-folders" title="ti ti-folders">Folder 1</option>
                                <option value="ti ti-folder-x" title="ti ti-folder-x">Folder 2</option>
                                <option value="ti ti-file" title="ti ti-file">Archivo 1</option>
                                <option value="ti ti-clipboard-text" title="ti ti-clipboard-text"> Archivo 2</option>                            
                                <option value="ti ti-building-community" title="ti ti-building-community">Edificio 3</option>
                                <option value="ti ti-briefcase" title="ti ti-briefcase">Maleta</option> 
                              </select>
                            </div>
                          </div> 
                          <!-- Color -->
                          <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                            <div class="form-group">
                              <label for="p_icono_color">Color</label>
                              <select class="form-control" name="icono_color" id="p_icono_color">
                                <option value="text-primary" title="ti ti-square-rounded-filled text-primary">Azul</option>
                                <option value="text-info" title="ti ti-square-rounded-filled text-info">Celeste</option>
                                <option value="text-warning" title="ti ti-square-rounded-filled text-warning">Amarillo</option>
                                <option value="text-success" title="ti ti-square-rounded-filled text-success">Verde</option>                            
                                <option value="text-danger" title="ti ti-square-rounded-filled text-danger">Rojo</option>
                                <option value="text-secondary" title="ti ti-square-rounded-filled text-secondary">Plomo</option>
                              
                              </select>
                            </div>
                          </div>  
                        </div>
                      </div> 

                      <div class="col-lg-12 col-md-12">
                        <div class="mt-3" id="resultado_crear_presupuesto"></div>
                      </div>                    

                      
                      <!-- barprogress -->
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="barra_progress_presupuesto_div" style="margin-top:20px; display:none;">
                        <div class="progress">
                          <div id="barra_progress_presupuesto" class="progress-bar" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
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
                    <button type="submit" style="display: none;" id="submit-form-presupuesto">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button"  class="btn btn-outline-danger " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" id="guardar_registro_presupuesto" class="btn btn-success" ><i class="ti ti-device-floppy"></i> Guardar</button>
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
  <script src="{{ asset('assets/js/presupuesto.js') }}?version_erp=01.02"></script>

</body>
</html>
