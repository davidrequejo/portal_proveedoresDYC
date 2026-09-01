<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Proveedores | Portal Proveedores D&C</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" type="image/png">

  @include('layouts.lte_head')


      <!-- BS Stepper -->
  <link rel="stylesheet" href="{{ asset('adminlte3/plugins/bs-stepper/css/bs-stepper.min.css') }}">

  <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
  <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"  rel="stylesheet" />
  <link href="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.css"  rel="stylesheet" />


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

    .bg-orange { background-color: #fd7e14 !important; color: #fff;}


    /**/
    .step-indicator {
      display: flex;
      gap: 10px;
    }

    .step-indicator .step {
      flex: 1;
      padding: 8px;
      text-align: center;
      background: #e9ecef;
      border-radius: 5px;
      font-weight: 600;
      opacity: 0.6;
    }

    .step-indicator .step.active {
      background: #007bff;
      color: #fff;
      opacity: 1;
    }

    .step-content {
      display: none;
    }

    .step-content.active {
      display: block;
    }


  </style>

</head>
<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed pace-orange">
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

    @if (auth()->user()->perm_client_vista_adm)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h4 class="m-0 text-black-50 Nombre_inicial">Cliente</h4>
              </div><!-- /.col -->
              <div class="col-sm-6">
                <div class="float-right">

                  <div class="btn-group btn-agregar-cliente">
                    <button type="button" class="btn btn-primary" style="border-color: #fcfcfc !important;" data-toggle="modal" data-target="#modal-agregar-cliente" onclick="limpiar_form_cliente();" ><i class="ti ti-users-plus"></i> Crear nuevo</button>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="border-color: #fcfcfc !important;">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal-agregar-cliente-masivo"><i class="ti ti-user-up"></i> Carga Masiva</a>
                      <div class="dropdown-divider my-0"></div>                  
                    </div>
                  </div>

                  <button type="button" class="btn btn-secondary btn-cancelar m-r-10px" onclick="show_hide_escenario(1);" style="display: none;"><i class="ri-arrow-left-line"></i> Regresar</button>

                </div>

                
              </div><!-- /.col -->

                            <!-- ./col -->
              <div class="col-12 filtros">
                <div class="card" style="margin-top: 0.5rem !important;">
                  
                  <div class="card-body"  style="padding-bottom: 1px; !important;">

                    <div class="p-1 " > 
                      <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                          <div class="activar-scroll-x-auto scroll-sm d-flex flex-nowrap gap-2">                                             
                            <!-- ::::::::::::::::::::: FILTRO estado_documento :::::::::::::::::::::: -->
                            <div style="width: 250px;  min-width: 200px;">
                              <div class="form-group">
                                <label for="descripcion"><span class="badge bg-info m-r-4px badge-new cursor-pointer" onclick="limpiarFiltro('estado_actualizaciondatos_filtro')" ><i class="las la-sync-alt"></i></span> Estado Sincronización</label>                          
                                <select name="estado_actualizaciondatos_filtro" id="estado_actualizaciondatos_filtro" class="form-control fs-h-input is-valid select2" placeholder="Estado" aria-invalid="false"  onchange="cargando_search(); delay(function(){filtros()}, 50 );">
                                  <option value="0" >Sin sincronizar S10</option>
                                  <option value="1" >Sincronizacion parcial S10</option>
                                  <option value="2" >Sincronizacion completa S10</option>
                                </select>
                              </div>
                            </div>

                            <div style="width: 250px;  min-width: 200px;">
                              <div class="form-group hidden">
                                <label for="descripcion"><span class="badge bg-info m-r-4px badge-new cursor-pointer" onclick="limpiarFiltro('tipo_persona_filtro')" ><i class="las la-sync-alt"></i></span> Tipo Persona</label>                          
                                <select name="tipo_persona_filtro" id="tipo_persona_filtro" class="form-control fs-h-input is-valid select2" placeholder="Estado" aria-invalid="false"  onchange="cargando_search(); delay(function(){filtros()}, 50 );">
                                  <option value="NATURAL">NATURAL</option>
                                  <option value="JURIDICA">JURIDICA</option>
                                </select>
                              </div>
                            </div>
                         
                          </div> 
                        </div>
                      </div>                                    
                                                      
                    </div>                   

                  </div>

                </div>
              </div>


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
              <div class="col" id="div-tabla-principal-cliente">
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
                        <button type="button" class="btn btn-sm btn-outline-info recargar-tabla-proveedor" data-toggle="tooltip" data-original-title="Recargar tabla" ><i class="ti ti-refresh"></i></button>
                      </div>
                    </div>
                    
                    <div class="table-responsive ">
                    
                      <table class="table table-bordered table-hover styletabla" id="tabla-proveedores">
                        <thead>
                          <tr>                        
                            <th style="padding: 8px 10px;">Acciones</th>
                            <th data-sort="codigo"      class="sortable" style="padding: 8px 10px;">Código S10</th>
                            <th data-sort="nombre_razonsocial" class="sortable" style="padding: 8px 10px;">Razón social</th>
                            <th data-sort="tipo_entidad_sunat"     class="sortable" style="padding: 8px 10px;">Tipo Entidad </th>
                            <th data-sort="abreviatura"     class="sortable" style="padding: 8px 10px;">Tipo de Doc.</th>
                            <th data-sort="numero_documento"class="sortable" style="padding: 8px 10px;">Nro de Doc.</th>
                            <th data-sort="celular"   class="sortable" style="padding: 8px 10px;">Teléfono</th>
                            <th data-sort="email"       class="sortable" style="padding: 8px 10px;">Email</th>
                            <th data-sort="direccion" class="sortable" style="padding: 8px 10px;">Dirección</th>
                          </tr>
                        </thead>
                        <tbody>                     
                        </tbody>
                      </table>
                    </div>

                  </div>
                  <!-- /.card-body -->
                  <div class="card-footer clearfix bg-color-white"  >
                    <ul class="pagination pagination-sm m-0 float-right" id="paginacion">
                      
                    </ul>
                  </div>
                </div>
              </div>

              <!--SINCRONIZACION CON EL S10--->
              <div class="col-lg-12 div_sincronizacions10" style="display: none;" >
                <div class="card">
                  
                  <div class="card-body pb-1">
                    <div class="row mb-2">                    

                      <div class="col-12  col-lg-6">

                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">DATOS DEL PROVEEDOR
                               <button type="button" class="btn btn-sm btn-principal btn_sincronizars10" onclick="sincronizarproveedors10()">Sincronizar Proveedor Con S10 </button>
                            </h3>
                          </div>
                          <!-- /.card-header -->
                          <div class="card-body">
                            <!-- we are adding the accordion ID so Bootstrap's collapse plugin detects it -->
                            <div id="accordion" class="lista_cambios_proveedor">


                            </div>
                          </div>
                          <!-- /.card-body -->
                        </div>


                      </div>

                      <div class="col-12  col-lg-6">

                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">DATOS CUENTAS BANCARIAS 
                              <button type="button" class="btn btn-sm btn-principal  btn_sincronizarcbs10" onclick="sincronizarcuentabancarias10()">Sincronizar Cuentas Bancarias Con S10</button>
                            </h3>

                          </div>
                          <!-- /.card-header -->
                          <div class="card-body">
                            <!-- we are adding the accordion ID so Bootstrap's collapse plugin detects it -->
                            <div id="accordion" class="lista_cuentas_bancarias_proveedor">
                              <div class="card">
                                <div class="card-header">
                                  <h4 class="card-title w-100">
                                    <a class="d-block w-100 text-principal text-bold" data-toggle="collapse" href="#collapseOne">
                                       <i class="fas fa-globe text-danger" ></i> Collapsible Group Item #1
                                    </a>
                                  </h4>
                                </div>
                                <div id="collapseOne" class="collapse show" data-parent="#accordion">
                                  <div class="card-body">
                                    <ul class="nav nav-pills flex-column">
                                      <li class="nav-item active">
                                        <p> <i class="far fa-circle text-secondary"></i> Razon Social : David Melvin Requejo Santa cruz </p>
                                        <p><i class="far fa-circle text-secondary"></i> Tipo Documento: RUC </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Nro Documento : 10745456015 </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Razon Social : David Melvin Requejo Santa cruz </p>
                                        <p><i class="far fa-circle text-secondary"></i> Tipo Documento: RUC </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Nro Documento : 10745456015 </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Razon Social : David Melvin Requejo Santa cruz </p>
                                        <p><i class="far fa-circle text-secondary"></i> Tipo Documento: RUC </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Nro Documento : 10745456015 </p>
                                      </li>
                                    </ul>
                                  </div>
                                </div>
                              </div>

                              <div class="card">
                                <div class="card-header">
                                  <h4 class="card-title w-100">
                                    <a class="d-block w-100 text-principal text-bold" data-toggle="collapse" href="#collapseThree">
                                      <i class="fas fa-globe text-success" ></i>  Collapsible Group Success
                                    </a>
                                  </h4>
                                </div>
                                <div id="collapseThree" class="collapse" data-parent="#accordion">
                                  <div class="card-body">
                                    <ul class="nav nav-pills flex-column">
                                      <li class="nav-item active">
                                        <p> <i class="far fa-circle text-secondary"></i> Razon Social : David Melvin Requejo Santa cruz </p>
                                        <p><i class="far fa-circle text-secondary"></i> Tipo Documento: RUC </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Nro Documento : 10745456015 </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Razon Social : David Melvin Requejo Santa cruz </p>
                                        <p><i class="far fa-circle text-secondary"></i> Tipo Documento: RUC </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Nro Documento : 10745456015 </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Razon Social : David Melvin Requejo Santa cruz </p>
                                        <p><i class="far fa-circle text-secondary"></i> Tipo Documento: RUC </p>
                                        <p> <i class="far fa-circle text-secondary"></i> Nro Documento : 10745456015 </p>
                                      </li>
                                    </ul>
                                  </div>
                                </div>
                              </div>

                            </div>
                          </div>
                          <!-- /.card-body -->
                        </div>

                      </div>

                    </div>

                  </div>

                </div>
              </div>


            </div>
            <!-- /.row -->
            
          </div><!-- /.container-fluid -->

          <div class="modal fade backdrop-filter-3px" id="modal-agregar-cliente">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header py-2 bg-color-principal" >
                  <h5 class="modal-title text-white">Cliente</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-white" aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-agregar-cliente" name="form-agregar-cliente" method="POST">
                    @csrf
                    <div class="row" id="cargando-1-formulario">
                      <!-- id persona -->
                      <input type="hidden" name="idpersona" id="idpersona" /> 
                      <input type="hidden" name="idtipo_persona" id="idtipo_persona" value="5" /> 


                      <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="descripcion">Tipo Entidad Sunat</label>                          
                          <!--<textarea class="form-control" name="descripcion" id="descripcion" cols="30" rows="1" placeholder="ejmpl. Los Jardines"></textarea>-->
                          <select name="tipo_entidad_sunat" id="tipo_entidad_sunat" class="form-control fs-h-input is-valid select2" placeholder="Tipo de documento" aria-invalid="false">
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
                          <select name="tipo_documento" id="tipo_documento" class="form-control fs-h-input is-valid select2" placeholder="Tipo de documento" aria-invalid="false">
                            <option value="6">RUC</option>
                          </select>
                        </div>
                      </div>

                      <!-- Nro de documento -->
                      <div class="col-12 col-sm-6 col-md-4 col-lg-4">

                        <div class="form-group">
                          <label for="descripcion">Nro de documento <sup class="text-danger">*</sup> <span class="d-inline-block text-danger" tabindex="0" data-toggle="tooltip" title="Estado Ruc"> 
                            <svg id="Capa_1" data-name="Capa 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 424.89 470.84" height="15px" width="15px" >
                              <defs>
                                <style>.cls-1{fill:#0056bd;}.cls-2{fill:#c70034;}</style>
                              </defs>
                              <path class="cls-1"
                                d="M316.23,295.78c-27.73-17.5-60.56-18.06-92.68-17.34L196,277.14h0c-45.82-6.24-69.94-32.37-63.93-80.46.83-6.57,1.44-13.16,2.75-25.29C106,198.54,80.49,222.18,55.37,246.2c-21.52,20.57-21.52,28.31-1,49.23C70.53,311.83,227.35,466.74,242,480.36c9.16,5.79,17.18,4.26,24.17-3.83h0c25.45-25.14,50.94-49,75.87-75.82C379.4,360.51,358.4,322.41,316.23,295.78Z"
                                transform="translate(-39.11 -13)" />
                              <path class="cls-2"
                                d="M458.49,213.31C446,200.62,283.58,34.17,274.75,28c-18-19.19-27.07-19.83-44.87-2.58C217.89,37,205.82,48.64,194.56,61c-17.18,18.83-39.87,33.3-47.55,59.84-2.78,12.55-3.22,18.63-.57,32h0c4.78,38,56.65,64.81,104.83,64.13L291.5,218h0c31.49-1.29,85.43-.13,86.83,49.84-.7,16.58-8.28,50.89-7.18,51.55.84.49,14.64-9.88,21.35-16.32C405.1,291,445.18,252.46,459.19,238,465.36,231.66,466.08,221,458.49,213.31Z"
                                transform="translate(-39.11 -13)" />
                            </svg>
                             </span> <span class="valido_novalido"> <span class="badge badge-secondary">Por Verificar</span> </span></label>                          
                           <div class="input-group">
                            
                              <input type="number" name="numero_documento" class="form-control fs-h-input" id="numero_documento" placeholder="N° de documento" onkeypress="return soloNumeros(event)" maxlength="11" />
                              <div class="input-group-append" data-toggle="tooltip" data-original-title="Buscar Reniec/SUNAT" onclick="buscar_sunat_reniec();">
                                <span class="input-group-text" style="cursor: pointer;">
                                  <i class="fas fa-search text-primary" id="search"></i>
                                  <i class="fa fa-spinner fa-pulse fa-fw fa-lg text-primary" id="charge" style="display: none;"></i>
                                </span>
                              </div>
                              <input type="hidden" class="input_hidden_ss"  id="estado_sunat"  />
                            </div>
                        </div>
                      </div>

                      <!-- Nombre y Apellidos -->
                      <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                        <div class="form-group">
                          <label for="Nombre_Apellidos">Razon Social <sup class="text-danger">*</sup></label>
                          <input type="text" name="nombre_razonsocial" class="form-control fs-h-input" id="nombre_razonsocial"  />
                        </div>
                      </div> 
                       <!-- Nombre apellidos maternos paternos  -->
                      <div class="col-12 col-sm-12 col-md-7 col-lg-7" style="display: none">
                        <div class="form-group">
                          <label for="Nombre_Apellidos">Nombre <sup class="text-danger">*</sup></label>
                          <input type="text" name="nombre_persona_natural" class="form-control fs-h-input" id="nombre_persona_natural"  />
                          <input type="text" name="apellido_paterno_per_natural" class="form-control fs-h-input" id="apellido_paterno_per_natural"  />
                          <input type="text" name="apellido_materno_per_natural" class="form-control fs-h-input" id="apellido_materno_per_natural"  />
                        </div>
                      </div> 
                      <!-- Teléfono -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label>Teléfono</label>
                          <input type="text" name="celular" id="celular" class="form-control fs-h-input" data-inputmask="'mask': ['999-999-999', '+51 999 999 999']" data-mask="" inputmode="text" onkeypress="return soloNumeros(event)">
                        </div>
                      </div>

                      <!-- email -->
                      <div class="col-12 col-sm-12 col-md-7 col-lg-7">
                        <div class="form-group">
                          <label for="email">Email</label>
                           <input type="email" name="email" class="form-control fs-h-input" id="email" placeholder="Correo electrónico" onkeyup="convert_minuscula(this);">
                        </div>
                      </div>   
                      <!-- email -->
                      <div class="col-12 col-sm-12 col-md-5 col-lg-5">
                        <div class="form-group">
                          <label for="sitio_web">Sitio web</label>
                           <input type="url" name="sitio_web" class="form-control fs-h-input" id="sitio_web" placeholder="www.tusitioweb.com">
                        </div>
                      </div>   

                      <!-- Dirección -->
                      <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                          <label for="direccion">Dirección </label> <br>
                          <textarea name="direccion" id="direccion" class="form-control" rows="1"></textarea>
                        </div>
                      </div>

                      <!-- Distrito -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="distrito">Distrito</label>
                          <select name="distrito" id="distrito" class="form-control fs-h-input select2" style="width: 100%;"  > </select>   
                        </div>
                      </div> 

                      <!-- Provincia -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="provincia">Provincia</label>
                          <input type="text" name="provincia" class="form-control fs-h-input" id="provincia"  readonly/>
                        </div>
                      </div> 

                      <!-- Departamento -->
                      <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                        <div class="form-group">
                          <label for="departamento">Departamento</label>
                          <input type="text" name="departamento" class="form-control fs-h-input" id="departamento"  readonly />
                        </div>
                      </div> 

                      <div class=" mb-3 col-12">
                        <div class="card-header font-weight-bold"  style="background-color: #e9ecef">Creación de Acceso al Portal <span class="text-center badge badge-info cursor-pointer" style=" font-size: 13px; pont" id="btn_generar_credenciales" >Generar credenciales</span></div>
                        
                        <div class="card-body text-secondary">
                          <p class="card-text">Genera automáticamente el usuario y contraseña a partir de los datos del Cliente.</p>

                          <div class="row">
                               <input type="hidden" name="id" id="id" />
                        
                              <div class="form-group col-md-6">
                                  <label>Usuario</label>
                                  <input type="text" id="usuario_portal" name="usuario_portal" class="form-control fs-h-input"
                                      placeholder="Usuario automático">
                              </div>

                              <div class="form-group col-md-6">
                                  <label>Contraseña</label>
                                  <input type="text" id="clave_portal" name="clave_portal" class="form-control fs-h-input"
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
                  <button type="button" class="btn btn-outline-secondary " data-dismiss="modal"><i class="ti ti-circle-dashed-x"></i>Cerrar</button>
                  <button type="button" class="btn btn-primary guardar_registro_cliente" style="display:none;" ><i class="ti ti-device-floppy"></i> Guardar</button>
                </div>
              </div>
              <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
          </div>
          <!-- /.modal -->

          <!--Carga Masiva de clientes-->
          <div class="modal fade backdrop-filter-3px" id="modal-agregar-cliente-masivo">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header py-2 bg-color-principal">
                  <h4 class="modal-title text-white text-bold">Importación de Clientes</h4>
                  <button type="button" class="close" data-dismiss="modal">
                    <span class="text-white">&times;</span>
                  </button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                  <!-- INDICADOR PASOS -->
                  <div class="step-indicator mb-3">
                    <div class="step active" data-step="1">1. Plantilla</div>
                    <div class="step" data-step="2">2. Importar Excel</div>
                  </div>

                  <!-- PASO 1 -->
                  <div class="step-content active" data-step="1">
                    <div class="form-group">
                      <label>Click para:</label>
                      <a href="{{ asset('assets/plantilla-excel/Plantilla-importar-cliente.xlsx') }}" download="Plantilla-Cliente">Descargar plantilla excel</a>
                    </div>

                    <div class="alert alert-warning">
                      <strong>Atención:</strong> Use la plantilla sin modificar columnas.
                    </div>

                    <button class="btn btn-primary btn-next">Siguiente</button>
                  </div>

                  <!-- PASO 2 -->
                  <div class="step-content" data-step="2">
                    <div class="form-group">

                      <input type="file" class="filepond"  name="file_excel_clientes_masivo" id="input-plantilla-excel"   data-allow-reorder="true"  data-max-files="1"  >
                            
                    </div>

                    <button class="btn btn-secondary btn-prev">Anterior</button>
                  </div>

                  <!-- PROGRESS -->
                  <div class="progress mt-3 d-none" id="barra_progress_importar_clientes_div">
                    <div id="barra_progress_importar_clientes"
                        class="progress-bar"
                        style="width:0%">0%</div>
                  </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer d-none" id="div-btn-importar-clientes">
                  <button class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cerrar</button>
                  <button class="btn btn-success btn-sm" id="guardar_registro_importar_clientes">
                    Importar
                  </button>
                </div>

              </div>
            </div>
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


    {{-- FilePond --}}
  
  <script src="{{ asset('adminlte3/plugins/filepond/dist/filepond.js') }}"></script>
  <script src="{{ asset('adminlte3/plugins/filepond/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js') }}"></script>
  <script src="{{ asset('adminlte3/plugins/filepond/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js') }}"></script>
  <script src="{{ asset('adminlte3/plugins/filepond/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js') }}"></script>
  <script src="{{ asset('adminlte3/plugins/filepond/filepond-plugin-image-validate-size/dist/filepond-plugin-image-validate-size.js') }}"></script>
  <script src="{{ asset('adminlte3/plugins/filepond/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.js') }}"></script>
  <script src="{{ asset('adminlte3/plugins/filepond/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js') }}"></script>

  <!-- BS-Stepper -->
  <script src="{{ asset('adminlte3/plugins/bs-stepper/js/bs-stepper.min.js') }}"></script>
    <!-- bs-custom-file-input -->
  <script src="{{ asset('adminlte3/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
 
  <script src="{{ asset('assets/js/cliente.js') }}?v={{ filemtime(public_path('assets/js/cliente.js')) }}"></script>
  <script src="{{ asset('assets/js/cliente_carga_masiva.js') }}?v={{ filemtime(public_path('assets/js/cliente_carga_masiva.js')) }}"></script>
  <script src="{{ asset('assets/js/sincronizacions10.js') }}?v={{ filemtime(public_path('assets/js/sincronizacions10.js')) }}"></script>
  

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip();

    // BS-Stepper Init
    document.addEventListener('DOMContentLoaded', function () {
      window.stepper = new Stepper(document.querySelector('.bs-stepper'))
    });
    // Input file
    bsCustomFileInput.init();
     
    });

      const idTipoPersonauser = {{ auth()->user()->persona->idtipo_persona }};

  </script>

</body>
</html>
