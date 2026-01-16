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


    .titulo-separador {
      display: flex;
      align-items: center;
      text-align: center;
      font-weight: 600;
      color: #082847;
    }

    .titulo-separador::before,
    .titulo-separador::after {
      content: "";
      flex: 1;
      border-bottom: 1px solid #ced4da;
    }

    .titulo-separador::before {
      margin-right: 12px;
    }

    .titulo-separador::after {
      margin-left: 12px;
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

    @if (auth()->user()->perm_client_vista_client)

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0">Actualizar Información Cliente</h1>
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
                  <div class=" col-12 col-sm-12 col-md-6 col-lg-5">
                    <div class="card">
                      <div class="card-header border-0" style="background-color: aliceblue;">
                        <h3 class="card-title m-2 font-weight-bold text-info">Información del Cliente</h3>
                        <div class="card-tools m-2"></div>
                      </div>
                      <div class="modal-body vista_inicial titulo-separador"><i class="fas fa-spinner fa-spin fa-lg " style="color: #e60f00;"></i>  <Span style="color: #e60f00;"> Cargando...</Span> </div>  
                      <div class="modal-body vista_datos" style="display: none">
                        <form id="form-editar-cliente" name="form-editar-cliente" method="POST">
                          @csrf
                          <div class="row" id="cargando-1-formulario">

                            <!-- id persona -->
                            <input type="hidden" name="idpersonaUpdate" id="idpersonaUpdate"  value="{{ auth()->user()->idpersona }}" />

                            <div class="col-12 pb-3">
                              <div class="titulo-separador">
                                <span class="text-bold text-info" >Datos Generales Cliente</span>
                              </div>
                            </div>

                            <!-- Tipo Entidad Sunat -->
                            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                              <div class="form-group">
                                <label for="descripcion">Tipo Entidad Sunat</label> 
                                <select name="tipo_entidad_sunat" id="tipo_entidad_sunat" class="form-control is-valid select2" placeholder="Tipo de documento" aria-invalid="false" readonly>
                                  <option value="NATURAL">NATURAL</option>
                                  <option value="JURIDICA">JURIDICA</option>
                                </select>
                              </div>
                            </div>
        
                            <!-- Tipo de documento -->
                            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                              <div class="form-group">
                                <label for="descripcion">Tipo de documento</label> 
                                
                                <select name="tipo_documento_input1" id="tipo_documento_input1" class="form-control is-valid select2" placeholder="Tipo de documento" aria-invalid="false" readonly>
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
                                    <input type="number" name="numero_documento_input1" class="form-control" id="numero_documento_input1" placeholder="N° de documento">
                                    <div class="input-group-append" data-toggle="tooltip" data-original-title="Buscar Reniec/SUNAT" onclick="buscar_sunat_reniec('_input1');">
                                      <span class="input-group-text" style="cursor: pointer;">
                                        <i class="fas fa-search text-primary" id="search"></i>
                                        <i class="fa fa-spinner fa-pulse fa-fw fa-lg text-primary" id="charge" style="display: none;"></i>
                                      </span>
                                    </div>
                                  </div>
                              </div>
                            </div>

                            <!-- Nombre y Apellidos -->
                            <div class="col-12 col-sm-12 col-md-8 col-lg-8 div_razon_social">
                              <div class="form-group">
                                <label for="Nombre_Apellidos">Nombre y Apellidos/Razon Social <sup class="text-danger">*</sup></label>
                                <input type="text" name="nombre_razonsocial_input1" class="form-control" id="nombre_razonsocial_input1"  />
                              </div>
                            </div> 


                            <div class="col-12 col-sm-12 col-md-4 col-lg-4  div_campos_pers_nat" style="display: none;">
                              <div class="form-group">
                                <label>Nombres</label>
                                <input type="text" name="nombre_persona_natural" id="nombre_persona_natural" class="form-control">
                              </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-4 col-lg-4  div_campos_pers_nat" style="display: none;">
                              <div class="form-group">
                                <label>Apellido Paterno</label>
                                <input type="text" name="apellido_paterno_per_natural" id="apellido_paterno_per_natural" class="form-control">
                              </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-4 col-lg-4  div_campos_pers_nat" style="display: none;">
                              <div class="form-group">
                                <label>Apellido Materno</label>
                                <input type="text" name="apellido_materno_per_natural" id="apellido_materno_per_natural" class="form-control">
                              </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-3 col-lg-3  div_campos_pers_nat" style="display: none;">
                              <div class="form-group">
                                <label>sexo</label>
                                <select name="sexo" id="sexo" class="form-control is-valid select2" placeholder="Sexo" aria-invalid="false">
                                  <option value="M">Masculino</option>
                                  <option value="F">Femenino</option>
                                </select>
                              </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-3 col-lg-3  div_campos_pers_nat" style="display: none;">
                              <div class="form-group">
                                <label>fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control">
                              </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-4 col-lg-4  div_campos_pers_nat" style="display: none;">
                              <div class="form-group">
                                <label>R.U.C <span class="d-inline-block text-danger" tabindex="0" data-toggle="tooltip" title="Click Para Verificar Ruc" onclick="buscar_sunat_reniec('_input2');"> <i class="fas fa-question-circle "></i> </span> <span class="valido_novalido"><span class="badge badge-secondary">Por Verificar</span></span>  </label>
                                <input type="hidden" id="tipo_documento_input2" class="form-control" value="6">
                                <input type="text" name="ruc_pers_nat" id="numero_documento_input2" class="form-control">
                              </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-2 col-lg-2  div_campos_pers_nat" style="display: none;">
                              <div class="form-group">
                                <label>Tratamiento </label>                                                            
                                <select name="tratamiento_pers_nat" id="tratamiento_pers_nat" class="form-control is-valid select2" placeholder="Tratamiento" aria-invalid="false">
                                  <option value="01">Ing.</option>
                                  <option value="02">Sr.</option>
                                  <option value="03">Srta.</option>
                                  <option value="04">Sra.</option>
                                  <option value="05">CPC</option>
                                  <option value="06">Don</option>
                                </select>
                              </div>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-12 col-sm-12 class_col_dni_ruc_telefono">
                              <div class="form-group">
                                <label for="celular">Teléfono</label>
                                <input type="text" name="celular" id="celular" class="form-control" data-inputmask="'mask': ['999-999-999', '+51 999 999 999']" data-mask="" inputmode="text">
                              </div>
                            </div>

                            <!-- email -->
                            <div class="col-12 col-sm-12  class_col_dni_ruc_email">
                              <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" class="form-control" id="email" placeholder="Correo electrónico" onkeyup="convert_minuscula(this);">
                              </div>
                            </div>   

                            <!-- Dirección -->
                            <div class="col-12 col-sm-12  class_col_dni_ruc_direccion">
                              <div class="form-group">
                                <label for="direccion">Dirección </label> <br>
                                <textarea name="direccion" id="direccion" class="form-control" rows="1"></textarea>
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

                            <div class="col-12 pb-3 div_campos_pers_jud" style="display: none;">
                              <div class="titulo-separador">
                                <span class="text-bold text-info" >Representante Legal</span>
                              </div>
                            </div>

                            <!-- Representante legal -->
                            <div class="col-12 col-sm-12 col-md-8 col-lg-8 div_campos_pers_jud" style="display: none;">
                              <div class="form-group">
                                <label for="nombre_apellidos_representante_legal">Representante legal</label>
                                <input type="text" name="nombre_apellidos_representante_legal" id="nombre_apellidos_representante_legal" class="form-control" placeholder="Nombre y Apellidos"  />
                              </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 div_campos_pers_jud" style="display: none;">
                              <div class="form-group">
                                <label for="telefono_representante">Teléfono Representante legal</label>
                                <input type="text" name="telefono_representante" id="telefono_representante" class="form-control" data-inputmask="'mask': ['999-999-999', '+51 999 999 999']" data-mask="" inputmode="text">
                              </div>
                            </div>

                            <div class="col-12 pb-3 div_campos_pers_jud" style="display: none;">
                              <div class="titulo-separador">
                                <span class="text-bold text-info" >Contacto Comercial</span>
                              </div>
                            </div>

                            <!-- Contacto comercial -->
                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 div_campos_pers_jud" style="display: none;">
                              <div class="form-group">
                                <label for="nombre_apellidos_contacto_comercial">Nombres  </label>
                                <input type="text" name="nombre_apellidos_contacto_comercial" id="nombre_apellidos_contacto_comercial" class="form-control" placeholder="Nombre y Apellidos"  />
                              </div>
                            </div>  

                            <!-- Cargo contacto comercial -->
                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 div_campos_pers_jud" style="display: none;">
                              <div class="form-group">
                                <label for="cargo_contacto_comercial">Cargo</label>
                                <input type="text" name="cargo_contacto_comercial" id="cargo_contacto_comercial" class="form-control" placeholder="Cargo"  />
                              </div>
                            </div>  

                            <!-- Teléfono contacto comercial -->
                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 div_campos_pers_jud" style="display: none;">
                              <div class="form-group">
                                <label for="telefono_contacto_comercial">Teléfono</label>
                                <input type="text" name="telefono_contacto_comercial" id="telefono_contacto_comercial" class="form-control" data-inputmask="'mask': ['999-999-999', '+51 999 999 999']" data-mask="" inputmode="text" placeholder="Teléfono">
                              </div>
                            </div>  

                            <!-- Email contacto comercial -->
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 div_campos_pers_jud" style="display: none;">
                              <div class="form-group">
                                <label for="email_contacto_comercial">Correo Electrónico</label>
                                <input type="email" name="email_contacto_comercial" class="form-control" id="email_contacto_comercial" placeholder="Correo electrónico" onkeyup="convert_minuscula(this);">
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
                          <button type="submit" style="display: none;" id="submit-form-editarcliente">Submit</button>
                        </form>

                      </div>
                      <div class="modal-footer justify-content-end py-1">
                        <button type="button" class="btn btn-success" id="editar_registro_cliente" ><i class="ti ti-device-floppy"></i> Actualizar</button>
                      </div>

                    </div>
                    <!-- /.card -->
                  </div>

                  <!--------------------------------------------------------------
                    ----------------------------------------------------------------
                                            CUENTAS BANCARIAS     
                    ----------------------------------------------------------------
                    ---------------------------------------------------------------->

                  <!-- /.col-md-6 -->
                  <div class=" col-12 col-sm-12 col-md-6 col-lg-7">
                    <div class="card">
                      <div class="card-header border-0" style="background-color: aliceblue;">
                              
                        <h3 class="card-title m-2 font-weight-bold text-info">Cuentas Bancarias
                        </h3>
                        <div class="float-right">

                          <div class="btn-group btn-agregar-proyecto">
                            <button type="button" class="btn btn-success" style="border-color: #1a6b2c !important;" data-toggle="modal" data-target="#modal-crear_cuentabancaria" onclick=""><i class="nav-icon fas fa-file"></i> Crear nuevo</button>
                          </div>
                        </div>
                        <div class="card-tools m-2"></div>

                      </div>



                      <div class="card-body">
                        <div class="card-body p-0">

                          <div class="card-body table-responsive ">
                            <table class="table table-striped table-valign-middle" id="tbl_lista_cuentas_bancarias">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th>Banco.</th>
                                  <th>Tipo Cuenta</th>
                                  <th>N° Cuenta</th>
                                  <th>Predeterminado</th>
                                  <th>Moneda</th>
                                  <th>InterBancario</th>
                                  <th>N° Cuenta Abono</th>
                                  <th class="text-center">Estado</th>
                                </tr>
                              </thead>
                              <tbody >

                              </tbody>
                            </table>
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


          <!-- MODAL CREAR/EDITAR CUENTA BANCARIA -->
          <div class="modal fade show" id="modal-crear_cuentabancaria"  aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h6 class="modal-title">Cuenta Bancaria</h6>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form id="form-cuenta-bancaria" method="POST">
                    @csrf

                    <!-- PK (hidden si es edición) -->
                    <input type="hidden" name="idpersona_CuentaBancaria" id="idpersona_CuentaBancaria">

                    <!-- Persona -->
                    <input type="text" name="idpersona" id="idpersona" value="{{ auth()->user()->idpersona }}">

                    <div class="row">

                      <!-- Banco -->
                      <div class="col-12 col-md-6 col-lg-6">
                        <div class="form-group">
                          <label for="idbanco">Banco</label>
                          <select name="idbanco" id="idbanco" class="form-control select2" required>
                          </select>
                        </div>
                      </div>

                      <!-- Tipo de cuenta -->
                      <div class="col-12 col-md-3 col-lg-4">
                        <div class="form-group">
                          <label for="tipocuenta">Tipo de Cuenta</label>
                          <select name="tipocuenta" id="tipocuenta"  class="form-control is-valid select2" placeholder="Tipo de Cuenta" aria-invalid="false">
                            <option value="C">Corriente</option>
                            <option value="A">Ahorros</option>
                            <option value="M">Maestra</option>
                            <option value="T">CTS</option>
                            <option value="D">Detracción</option>
                            <option value="S">Cuenta Sueldo</option>

                          </select>
                        </div>
                      </div>

                      <!-- Moneda -->
                      <div class="col-12 col-md-3 col-lg-2">
                        <div class="form-group">
                          <label for="moneda">Moneda</label>
                          <select name="moneda" id="moneda"  class="form-control is-valid select2" placeholder="Moneda" aria-invalid="false">
                            <option value="">Seleccione</option>
                            <option value="01">S/.</option>
                            <option value="02">U$</option>
                          </select>
                        </div>
                      </div>

                      <!-- Predeterminado -->
                      <div class="col-12 col-md-3 col-lg-2">
                        <div class="form-group">
                          <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="cuenta predeterminada">
                           <label for="predeterminado">Pred?</label>
                          </span>
                          <select name="predeterminado" id="predeterminado"  class="form-control is-valid select2" placeholder="Cuenta Predeterminada" aria-invalid="false">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                          </select>
                        </div>
                      </div>

                      <!-- Número de cuenta -->
                      <div class="col-12 col-md-4 col-lg-5">
                        <div class="form-group">
                          <label for="numero_cuenta">Número de Cuenta</label>
                          <input type="number" name="numero_cuenta" id="numero_cuenta" class="form-control" maxlength="45" placeholder="Ej: 12345678900" required  onkeypress="return soloNumeros(event)">
                        </div>
                      </div>

                      <!-- Cuenta Interbancaria -->
                      <div class="col-12 col-md-5 col-lg-5">
                        <div class="form-group">
                          <label for="cuenta_interbancaria">CCI</label>
                          <input type="number" name="cuenta_interbancaria" id="cuenta_interbancaria" class="form-control" maxlength="45" placeholder="Código de Cuenta Interbancaria"  onkeypress="return soloNumeros(event)">
                        </div>
                      </div>

                    </div>

                    <!-- /.card-body -->
                    <button type="submit" style="display: none;" id="submit-cuentabancaria">Submit</button>

                  </form>

                </div>
                <div class="modal-footer justify-content-between">
                  <button type="button" class="btn btn-default" data-dismiss="modal" onclick="">Cerrar</button>
                  <button type="button" class="btn btn-success" id="guardar_registro_cuenta_bank">Guardar</button>
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


  <script src="{{ asset('assets/js/actualizardatoscliente.js') }}?version_erp=01.03"></script>
  <script src="{{ asset('assets/js/persona_cuentabancaria.js') }}?version_erp=01.03"></script>

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  </script>

</body>
</html>
