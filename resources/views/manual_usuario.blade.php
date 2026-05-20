<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>Inicio | Portal</title>

    <link rel="icon" href="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" type="image/png">

    @include('layouts.lte_head')
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

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">

                  <div class="row">
                    <div class="col-12">
                      <div class="card shadow-sm " style="background-color: #f4f6f9">

                        <div class="row">

                        @if(auth()->user()->persona->idtipo_persona == 2)
                        <div class="col-12 col-sm-3">
                          <a href="{{ asset('assets/manual_usuario/Rol_administrador.pdf') }}" target="_blank">
                            <div class="info-box bg-light" style="cursor: pointer;">
                                <div class="info-box-content">
                                    <span class="info-box-text text-center text-muted">Manual Usuario Rol</span>
                                    <span class="info-box-number text-center text-muted mb-0">Administrador</span>
                                </div>
                            </div>
                          </a>
                        </div>
                        @endif

                        @if(auth()->user()->persona->idtipo_persona == 2 || auth()->user()->persona->idtipo_persona == 6)
                          <div class="col-12 col-sm-3">
                            <a href="{{ asset('assets/manual_usuario/Rol_comprador.pdf') }}" target="_blank">
                              <div class="info-box bg-light" style="cursor: pointer;">
                                  <div class="info-box-content">
                                      <span class="info-box-text text-center text-muted">Manual Usuario Rol</span>
                                      <span class="info-box-number text-center text-muted mb-0">Comprador</span>
                                  </div>
                              </div>
                            </a>
                          </div>
                        @endif

                        @if(auth()->user()->persona->idtipo_persona == 2 || auth()->user()->persona->idtipo_persona == 3)
                          <div class="col-12 col-sm-3">
                            <a href="{{ asset('assets/manual_usuario/Rol_proveedor.pdf') }}" target="_blank">
                              <div class="info-box bg-light" style="cursor: pointer;">
                                  <div class="info-box-content">
                                      <span class="info-box-text text-center text-muted">Manual Usuario Rol</span>
                                      <span class="info-box-number text-center text-muted mb-0">Proveedor</span>
                                  </div>
                              </div>
                            </a>
                          </div>
                        @endif

                        @if(auth()->user()->persona->idtipo_persona == 2 || auth()->user()->persona->idtipo_persona == 5)
                          <div class="col-12 col-sm-3">
                            <a href="{{ asset('assets/manual_usuario/Rol_cliente.pdf') }}" target="_blank">
                              <div class="info-box bg-light" style="cursor: pointer;">
                                  <div class="info-box-content">
                                      <span class="info-box-text text-center text-muted">Manual Usuario Rol</span>
                                      <span class="info-box-number text-center text-muted mb-0">Cliente</span>
                                  </div>
                              </div>
                            </a>
                          </div>
                        @endif

                        </div>

                      </div>
                    </div>

                  </div>

                  

                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->


        @include('layouts.lte_footer')

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    @include('layouts.lte_script')

</body>

</html>
