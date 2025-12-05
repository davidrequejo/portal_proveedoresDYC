<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Proveedores | Portal Proveedores D&C</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/ico-opt.png') }}" type="image/png">

  @include('layouts.lte_head')
  <!--<link rel="stylesheet" href="{{ asset('assets/jstree-3.3.17/dist/themes/default/style.min.css') }}" />-->

  <style>
    #tabla-proveedores_filter { width: calc(100% - 10px) !important; display: flex !important; justify-content: space-between !important; }
    #tabla-proveedores_filter label { width: 100% !important;  }
    #tabla-proveedores_filter label input { width: 100% !important;   }

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

    @if (auth()->user()->perm_proveedor)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0">Proveedores</h1>
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
                    
                      <table class="table table-bordered table-hover" id="tabla-proveedores">
                        <thead>
                          <tr>                        
                            <th>Acciones</th>
                            <th data-sort="codigo"      class="sortable">Código</th>
                            <th data-sort="nombre_razonsocial" class="sortable">Razón social</th>
                            <th data-sort="tipo_entidad_sunat"     class="sortable">Tipo Entidad Sunat </th>
                            <th data-sort="abreviatura"     class="sortable">Tipo de Documento</th>
                            <th data-sort="numero_documento"class="sortable">Nro de Documento</th>
                            <th data-sort="celular"   class="sortable">Teléfono</th>
                            <th data-sort="email"       class="sortable">Email</th>
                            <th data-sort="direccion" class="sortable">Dirección</th>
                            <th data-sort="estado" class="sortable">Estado Documentos</th>
                            
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
                  <h4 class="modal-title">Proveedores</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-danger" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-proveedor" name="form-agregar-proveedor" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">
                      <!-- id persona -->
                      <input type="hidden" name="idpersona" id="idpersona" /> 
                      <input type="hidden" name="idbanco" id="idbanco" value="1" /> 
                      <input type="hidden" name="idtipo_persona" id="idtipo_persona" value="3" /> 
                      
                      <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="descripcion">Tipo Estandar </label>                          
                          <!--<textarea class="form-control" name="descripcion" id="descripcion" cols="30" rows="1" placeholder="ejmpl. Los Jardines"></textarea>-->
                          <select name="idtipoestadandarproveedor" id="idtipoestadandarproveedor" class="form-control is-valid select2" placeholder="Tipo de documento" aria-invalid="false">
                          </select>
                        </div>
                      </div>

                      <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="descripcion">Tipo Entidad Sunat</label>                          
                          <!--<textarea class="form-control" name="descripcion" id="descripcion" cols="30" rows="1" placeholder="ejmpl. Los Jardines"></textarea>-->
                          <select name="tipo_entidad_sunat" id="tipo_entidad_sunat" class="form-control is-valid select2" placeholder="Tipo de documento" aria-invalid="false">
                            <option value="NATURAL">NATURAL</option>
                            <option value="JURIDICA">JURIDICA</option>
                          </select>
                        </div>
                      </div>
  
                      <!-- Tipo de documento -->
                      <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="descripcion">Tipo de documento</label>                          
                          <!--<textarea class="form-control" name="descripcion" id="descripcion" cols="30" rows="1" placeholder="ejmpl. Los Jardines"></textarea>-->
                          <select name="tipo_documento" id="tipo_documento" class="form-control is-valid select2" placeholder="Tipo de documento" aria-invalid="false">
                            <option value="1">DNI</option>
                            <option value="6">RUC</option>
                          </select>
                        </div>
                      </div>

                      <!-- Nro de documento -->
                      <div class="col-12 col-sm-6 col-md-5 col-lg-5">
                        <div class="form-group">
                          <label for="descripcion">Nro de documento <sup class="text-danger">*</sup></label>                          
                           <div class="input-group">
                              <input type="number" name="numero_documento" class="form-control" id="numero_documento" placeholder="N° de documento">
                              <div class="input-group-append" data-toggle="tooltip" data-original-title="Buscar Reniec/SUNAT" onclick="buscar_sunat_reniec();">
                                <span class="input-group-text" style="cursor: pointer;">
                                  <i class="fas fa-search text-primary" id="search"></i>
                                  <i class="fa fa-spinner fa-pulse fa-fw fa-lg text-primary" id="charge" style="display: none;"></i>
                                </span>
                              </div>
                            </div>
                        </div>
                      </div>

                      <!-- Nombre y Apellidos -->
                      <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                        <div class="form-group">
                          <label for="Nombre_Apellidos">Nombre y Apellidos/Razon Social <sup class="text-danger">*</sup></label>
                          <input type="text" name="nombre_razonsocial" class="form-control" id="nombre_razonsocial"  />
                        </div>
                      </div> 
                      <!-- Teléfono -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="celular">Teléfono</label>
                          <input type="text" name="celular" id="celular" class="form-control" data-inputmask="'mask': ['999-999-999', '+51 999 999 999']" data-mask="" inputmode="text">
                        </div>
                      </div>
                      <!-- Dirección -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="direccion">Dirección </label> <br>
                          <textarea name="direccion" id="direccion" class="form-control" rows="1"></textarea>
                        </div>
                      </div>

                      <!-- email -->
                      <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="email">Email</label>
                           <input type="email" name="email" class="form-control" id="email" placeholder="Correo electrónico" onkeyup="convert_minuscula(this);">
                        </div>
                      </div>                       
                      <!-- Distrito -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="distrito">Distrito</label>
                          <select name="distrito" id="distrito" class="form-control select2" style="width: 100%;"  > </select>   
                        </div>
                      </div> 

                      <!-- Provincia -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="provincia">Provincia</label>
                          <input type="text" name="provincia" class="form-control" id="provincia"  readonly/>
                        </div>
                      </div> 

                      <!-- Departamento -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="departamento">Departamento</label>
                          <input type="text" name="departamento" class="form-control" id="departamento"  readonly />
                        </div>
                      </div> 

                      <div class="card border-info mb-3 col-12">
                        <div class="card-header bg-color-0202022e font-weight-bold">Creación de Acceso al Portal <span class="text-center badge badge-info cursor-pointer" style=" font-size: 14px; pont" id="btn_generar_credenciales" >Generar credenciales</span></div>
                        <div class="card-body text-secondary">
                          <p class="card-text">Genera automáticamente el usuario y contraseña a partir de los datos del proveedor.</p>

                          <div class="row">

                              <div class="form-group col-md-6">
                                  <label>Usuario</label>
                                  <input type="text" id="usuario_portal" name="usuario_portal" class="form-control"
                                      placeholder="Usuario automático">
                              </div>

                              <div class="form-group col-md-6">
                                  <label>Contraseña</label>
                                  <input type="text" id="clave_portal" name="clave_portal" class="form-control"
                                      placeholder="Contraseña automática">
                              </div>
                          </div> 
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
                    <button type="submit" style="display: none;" id="submit-form-proveedor">Submit</button>
                  </form>
                </div>
                <div class="modal-footer justify-content-between py-1">
                  <button type="button" class="btn btn-outline-danger " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" class="btn btn-success" id="guardar_registro_proveedor" ><i class="ti ti-device-floppy"></i> Guardar</button>
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


  <script src="{{ asset('assets/js/proveedor.js') }}?version_erp=01.02"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
