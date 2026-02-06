<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Cargar Documentos | Portal Proveedores D&C</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" type="image/png">

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

    <!-- Navbar -->
    @include('layouts.lte_nav')  
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    @include('layouts.lte_aside')   

    @if (auth()->user()->perm_proveedor_vista_documentos_client)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
          </div><!-- /.container-fluid -->
        </div>

        <!-- Main content -->
        <section class="content">

          <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">

              <div class="col-lg-12" id="div-ver-documentos">

                <div class="row">

                  <div class="col-12 div_view_periodos_homologacion ">

                      <div class="col-sm-12 col-md-9 col-lg-9">
                        <div class="card">
                          <div class="card-header border-0"  style="background-color: aliceblue;">
                                  
                            <h3 class="card-title m-2 font-weight-bold text-info">Mis Periodos de Homologación
                            </h3>
                          </div>
                          <div class="card-body table-responsive styletabla">
                            <table class="table table-striped table-valign-middle">
                              <thead>
                              <tr>
                                <th>#</th>
                                <th class="text-center">Ver</th>
                                <th >Descripcion</th>
                                <th >Fecha Inicio Proceso</th>
                                <th >Fecha Inicio Homologación</th>
                                <th >Fecha Fin Homologación</th>
                                <th class="text-center">Estado</th>
                              </tr>
                              </thead>
                              <tbody class="tbl_lista_periodos_homologacion">
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

                  </div>

                  <div class="col-12 btn_regresar_principal " >
                    <div class="card  m-3 p-2  bg-color-principal">

                    <div style="display: flex;  align-items: center;">
                      <h3 >
                                                
                        <button type="button" class="btn btn-secondary btn-lg btn-cancelar m-r-10px" onclick="show_hide_escenario(1);" style="display: none;"><i class="ri-arrow-left-line"></i> Regresar</button>
                        
                        <h4 class="nombre_periodo_homologacion text-white text-bold"></h4>
                      </h3>
                    </div>
                    </div>
      

                  </div>

                  <div class="col-12 div_view_subir_documentos" style="display: none" >
                    <div class="row">

                      <div class="col-lg-6">
                        <div class="card">
                          <div class="card-header border-0" >
                                  
                            <h3 class="card-title m-2 font-weight-bold text-principal">Mis Documentos
                            </h3>
                            <div class="float-right">
                            </div>

                            <div class="card-tools m-2"></div>

                          </div>
                          <div class="card-body table-responsive ">
                            <table class="table table-striped table-valign-middle">
                              <thead>
                              <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Descripción Doc.</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Modelo</th>
                                <th class="text-center">Ver</th>
                                <th class="text-center">Actualizar</th>
                              </tr>
                              </thead>
                              <tbody class="tbl_lista_documentos">
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
                          <div class="card-header border-0" >
                            <h3 class="card-title m-2 font-weight-bold text-principal nombre_documento_pdf">Documento PDF</h3>
                          </div>

                          <div class="card-body">
                            <div class="card-body p-0 mostrar_documento_pdf" style="height: 750px;">
                              <div class="alert bg-color-principal text-white alert-dismissible">
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
                
              </div>

            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->


          <div class="modal fade show" id="modal-crear_documento"  aria-modal="true" role="dialog">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title">Cargar Documento</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-documentos-proveedor" name="form-documentos-proveedor" method="POST">
                    @csrf
                    <input type="hidden" name="iddocsproveedortipoestandar" id="iddocsproveedortipoestandar" /> 
                    <input type="hidden" name="nombre_seleccion_tipo" id="nombre_seleccion_tipo" /> 
                    <!-- $r->user()->idpersona; -->
                    <input type="hidden" name="idpersona_facha_homologacion" id="idpersona_facha_homologacion"  >

                    <div  class="row" id="cargando-1-formulario">

                      <div class="col-12">
                        <div class="form-group">
                          <label for="descripcion">Tipo Documento</label>                          
                            <input type="text" class="form-control  fs-h-input nombre_tipo_documento" placeholder="ejmpl. Los Jardines" readonly>
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
                          <img src="/assets/svg/pdf.svg" alt="" width="50%" />
                        </div>
                        <div class="text-center" id="doc1_nombre"><!-- aqui va el nombre del pdf --></div>
                      </div>

                      <div class="col-12 mt-2" hidden>
                        <div class="form-group">
                          <label for="descripcion">Descripción </label> <br>
                          <textarea name="descripcion" id="descripcion" class="form-control fs-h-input" rows="2"></textarea>
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


                    <button type="submit" style="display: none;" id="submit-form-docs_prov">Submit</button>

                  </form>



                </div>
                <div class="modal-footer justify-content-between">
                  <button type="button" class="btn btn-default" data-dismiss="modal" onclick="limpiar_form_subir_doc();">Cerrar</button>
                  <button type="button" class="btn btn-success" id="guardar_registro_docs_prov">Guardar</button>
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


  <script src="{{ asset('assets/js/subirdocs.js') }}?version_erp=01.06"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
