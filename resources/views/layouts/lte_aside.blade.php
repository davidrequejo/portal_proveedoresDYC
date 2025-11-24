  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="{{ asset('assets/images/brand-logos/ico-opt.png') }}" alt="Optimiza logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">ERP Optimiza</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{ asset('adminlte3/dist/svg/user_default.svg') }}" onerror="this.src='{{ asset('adminlte3/dist/svg/user_default.svg') }}';" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">{{ Auth::user()->name }}</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Buscar modulo..." aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->

          
          @if (auth()->user()->perm_presupuesto)
          <li class="nav-item">
            <a href="{{ route('presupuestos.index') }}" class="nav-link {{ request()->routeIs('presupuestos.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p> Presupuestos <span class="right badge badge-danger">New</span>  </p>
            </a>
          </li>
          @endif

          @if (auth()->user()->perm_proyecto)
          <li class="nav-item">
            <a href="{{ route('proyectos.index') }}" class="nav-link {{ request()->routeIs('proyecto.*') ? 'active' : '' }}">
              <i class="nav-icon ti ti-buildings"></i>
              <p> Proyectos <span class="right badge badge-danger">New</span>  </p>
            </a>
          </li>
          @endif

          @if (auth()->user()->perm_recurso)
          <li class="nav-item">
            <a href="{{ route('recursos.index') }}" class="nav-link {{ request()->routeIs('recursos.*') ? 'active' : '' }}">
              <i class="nav-icon ti ti-keyframes"></i>
              <p> Recursos <span class="right badge badge-danger">New</span>  </p>
            </a>
          </li>
          @endif

          

          @if (auth()->user()->perm_configuracion)
          <li class="nav-header">DATOS DE CONFIGURACIÓN</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon ti ti-settings"></i> <p>Configuracion<i class="fas fa-angle-left right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/tables/simple.html" class="nav-link"><i class="ti ti-calendar nav-icon"></i><p>Periodos</p></a>
              </li>
              <li class="nav-item">
                <a href="pages/tables/data.html" class="nav-link"><i class="ti ti-user-cog nav-icon"></i><p>Tipo socio Negocio</p></a>
              </li>              
            </ul>
          </li>
          @endif

          @if (auth()->user()->perm_utilitario)
          <li class="nav-item">
            <a href="#" class="nav-link activeE"> <i class="nav-icon fas fa-columns"></i> <p> Utilitarios <i class="right fas fa-angle-left"></i> </p> </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./index.html" class="nav-link activeE">
                  <i class="ti ti-users nav-icon"></i>
                  <p>Socio Negocio</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index2.html" class="nav-link">
                  <i class="ti ti-user-shield nav-icon"></i>
                  <p>Usuarios del sistema</p>
                </a>
              </li>              
            </ul>
          </li>    
          @endif
          
          <li class="nav-header">EXTRAS</li>

          @if (auth()->user()->perm_importar_hora)
          <li class="nav-item">
            <a href="{{ route('importar-horas.index') }}" class="nav-link {{ request()->routeIs('importar-horas.*') ? 'active' : '' }}">
              <i class="nav-icon ti ti-file-excel"></i>
              <p> Importar Horas <span class="right badge badge-danger">New</span>  </p>
            </a>
          </li>
          @endif

          @if (auth()->user()->perm_combinar_txt)
          <li class="nav-item">
            <a href="{{ route('combinar-txt.index') }}" class="nav-link {{ request()->routeIs('combinar-txt.*') ? 'active' : '' }}">
              <i class="nav-icon ti ti-file-excel"></i>
              <p> Combinar TXT <span class="right badge badge-danger">New</span>  </p>
            </a>
          </li>
          @endif

          @if (auth()->user()->perm_combinar_txt)
          <li class="nav-item">
            <a href="{{ route('combinar-planilla-afp.index') }}" class="nav-link {{ request()->routeIs('combinar-planilla-afp.*') ? 'active' : '' }}">
              <i class="nav-icon ti ti-file-excel"></i>
              <p> Combinar Planilla APF   </p>
            </a>
          </li>
          @endif
          
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>