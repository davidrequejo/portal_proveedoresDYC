<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>403 Error Page</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">404 Error Page</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="error-page">
        <h2 class="headline text-warning"> 403</h2>

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Selecionen otro escenario.</h3>
          <p>  
            Por favor, selecciona otro escenario para continuar. Mientras tanto, puedes <a  href="{{ url('/') }}">volver al panel</a> o contactar al administrador.
          </p>        
        </div>
        <!-- /.error-content -->
      </div>           

      <!-- /.error-page -->
    </section>

    <section class="content">
      <div class="container-fluid">     
        <div class="row">

          @if (auth()->user()->perm_presupuesto)
            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
             

              <a href="{{ route('presupuestos.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="far fa-envelope"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Presupuestos</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif

          @if (auth()->user()->perm_proyecto)
            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
              <a href="{{ route('proyectos.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="nav-icon ti ti-buildings"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Proyectos</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif

          @if (auth()->user()->perm_recurso)
            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
              <a href="{{ route('recursos.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="nav-icon ti ti-keyframes"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Recursos</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif

          @if (auth()->user()->perm_configuracion)
            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
              <a href="{{ route('importar-horas.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="ti ti-calendar nav-icon"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Periodos</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
              <a href="{{ route('importar-horas.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="ti ti-user-cog nav-icon"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Tipo socio Negocio</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif
          @if (auth()->user()->perm_utilitario)
            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
              <a href="{{ route('importar-horas.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="ti ti-users nav-icon"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Socio Negocio</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>

            <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
              <a href="{{ route('importar-horas.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="ti ti-user-shield nav-icon"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Usuarios del sistema</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif

          @if (auth()->user()->perm_importar_hora)
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">             

              <a href="{{ route('importar-horas.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="nav-icon ti ti-file-excel"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Importar horas</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif

          @if (auth()->user()->perm_combinar_txt)
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">             

              <a href="{{ route('combinar-txt.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="nav-icon ti ti-file-excel"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Combinar TXT</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif

          @if (auth()->user()->perm_combinar_txt)
            <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">             

              <a href="{{ route('combinar-planilla-afp.index') }}">         
                <div class="info-box">
                  <span class="info-box-icon bg-info"><i class="nav-icon ti ti-file-excel"></i></span>
                  <div class="info-box-content">
                    <span class="" style="line-height: 20px !important;">Combinar Excel AFP</span>                
                  </div>
                  <!-- /.info-box-content -->
                </div>
              </a>
              <!-- /.info-box -->
            </div>
          @endif
          
        </div>
      </div>
    </section>

    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->