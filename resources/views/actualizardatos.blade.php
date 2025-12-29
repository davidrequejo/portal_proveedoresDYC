<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Actualizar información | Portal Proveedores D&C</title>

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
                <h1 class="m-0">Actualizar Información Proveedor</h1>
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

              <div class="col-lg-12" id="div-ver-detalle-documentos">

                <div class="row">
                  <div class="col-lg-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <h3 class="card-title m-2 font-weight-bold text-info">Información del Proveedor</h3>
                        <div class="card-tools m-2"></div>
                      </div>
                      <div class="card-body table-responsive ">
                        <table class="table table-striped table-valign-middle">
                          <thead>
                          <tr>
                            <th>#</th>
                            <th>Descripcion Doc.</th>
                            <th>Estado</th>
                            <th>Ver</th>
                            <th class="text-center">Act. Estado</th>
                          </tr>
                          </thead>
                          <tbody class="tbl_lista_documentos">
                          <tr>
                             <td>001</td>
                            <td>
                              <img src="/assets/images/default/pdf_icon.png" alt="Product 1" class="img-circle img-size-32 mr-2">
                              Fecha Ruc de la empresa
                            </td>
                            <td><span class="badge bg-success">Verificado</span></td>
                            <td>
                              <a href="#" class="text-muted">
                                <i class="fas fa-search"></i>
                              </a>
                            </td>
                            <td class="text-center">
                              <a class="btn btn-info btn-sm" href="#"><i class="fas fa-pencil-alt"></i> Editar</a>                              
                            </td>
                          </tr>

                          </tbody>
                        </table>
                      </div>
                      <div class="modal-footer justify-content-end">
                        <!--<button type="button" class="btn btn-outline-warning" data-dismiss="modal">Close</button>
                        <button type="button " class="btn btn-sm btn-outline-success">Guardar</button>-->
                      </div>
                    </div>
                    <!-- /.card -->
                  </div>
                  <!-- /.col-md-6 -->
                  <div class="col-lg-6">
                    <div class="card">
                      <div class="card-header border-0">
                        <h3 class="card-title m-2 font-weight-bold text-info nombre_documento_pdf">Cuentas Bancarias</h3>
                      </div>

                      <div class="card-body">
                        <div class="card-body p-0 mostrar_documento_pdf" style="height: 650px;">
                          <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true" disabled></button>
                            <h5><i class="icon fas fa-info"></i> Sin vista previa!</h5>
                            Para visualizar un documento, haga clic en el ícono <i class="fas fa-search"></i> ubicado en la fila correspondiente de la tabla del lado izquierdo
                          </div>
                        </div>
                      </div>
                    </div>


                  </div>
                  <!-- /.col-md-6 -->
                </div>

                
              </div>
            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->


        <div class="modal fade show" id="modal-actualizar-estado"  aria-modal="true" role="dialog">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h6 class="modal-title">Actualizar Estado : <strong class='text-info nombre_doc_edit'></strong> </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="form-actualizar-estado" name="form-actualizar-estado" method="POST">
                  @csrf
                  <input type="hidden" name="idpersona_estado" id="idpersona_estado" /> 

                  <div class="form-group">

                    <div class="col-12">
                      <label for="estado_documentos">Estado de Documentos</label>
                      <select name="estado_documentos_update" id="estado_documentos_update" class="form-control is-valid select2" placeholder="Estado de Documentos" aria-invalid="false">
                        <option value="Registrado">Registrado</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Aprobado">Aprobado</option>
                        <option value="Rechazado">Rechazado</option>
                      </select>
                    </div>

                    <div class="col-12 mt-2">
                      <div class="form-group">
                        <label for="direccion">Dirección </label> <br>
                        <textarea name="direccion" id="direccion" class="form-control" rows="2"></textarea>
                      </div>
                    </div>

                  </div>
                </form>



              </div>
              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success">Guardar</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>

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


  <script src="{{ asset('assets/js/subirdocs.js') }}?version_erp=01.02"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
