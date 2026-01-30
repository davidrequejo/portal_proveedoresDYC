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
                <div class="card shadow-sm border-0" style="background-color: #f4f6f9">

                  <div class="card-body d-flex flex-column justify-content-center" style="min-height: calc(100vh - 180px);">
                    <div class="row align-items-center w-100">

                      <!-- TEXTO -->
                      <div class="col-md-12 text-center">
                        <h1 class="fw-bold text-principal mb-3" style="font-size: 5.0rem; ">
                          Bienvenido al Portal
                        </h1>

                        <h3 class="fw-semibold text-principal mb-4" style="font-size: 6.0rem; ">
                           De Homologación
                        </h3>

                        <p class="text-muted mb-5 " style="font-size: 1.3rem; ">
                           para Clientes, Proveedores, Documentación y Procesos internos de la empresa.
                        </p>

                        <div class="d-flex justify-content-center gap-3">
                            <img
                            src="{{ asset('assets/images/brand-logos/logo-grpo-inmobiliario-dc_dark.svg') }}"
                            alt="DC Grupo Inmobiliario"
                            class="img-fluid"
                            style="max-height: 200px;"
                          >
                        </div>

                        <div class="d-flex d-flex justify-content-end gap-3 mt-3">
                          <a href="https://optimiza360.pe/" target="_blank" rel="noopener noreferrer">
                            <img
                            src="{{ asset('assets/images/brand-logos/logo-principal-optimiza.png') }}"
                            alt="Optimiza 360"
                            class="img-fluid"
                            style="max-height: 50px;"
                          >
                          </a>
                        </div>
                        
                      </div>

                    </div>
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
