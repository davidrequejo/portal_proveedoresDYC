  @php
    $user = auth()->user();

    $puedeDocumentos =
        $user->perm_proveedor_vista_documentos_client &&
        $user->tiene_cuenta_bancaria &&
        $user->persona?->estado_completoxproveedor == 1;
  @endphp
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="{{ asset('assets/images/brand-logos/dc-logo_cirsulo_white.png') }}" alt="Optimiza logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Portal Homologación</span>
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
            <button class="btn btn-xs btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
              <a href="{{ route('inicio.index') }}" class="nav-link {{ request()->routeIs('inicio.*') ? 'active' : '' }}">
                <i class=" nav-icon fas fa-home"></i>
                <p> Inicio</p>
              </a>
            </li>
          <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
            @if (auth()->user()->perm_proveedor_vista_adm)
            <li class="nav-item">
              <a href="{{ route('proveedor.index') }}" class="nav-link {{ request()->routeIs('proveedor.*') ? 'active' : '' }}">
                <i class="nav-icon ti ti-user-cog"></i>
                <p> Proveedor</p>
              </a>
            </li>
            @endif


            @if (auth()->user()->perm_proveedor_vista_act_datos_client)
            <li class="nav-item">
              <a href="{{ route('actualizardatos.index') }}" class="nav-link {{ request()->routeIs('actualizardatos.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-edit"></i>
                <p> Actualizar Datos</p>
              </a>
            </li>
            @endif

             @if (auth()->user()->perm_proveedor_vista_documentos_client)
            <li class="nav-item">
              @if($puedeDocumentos)
                  <a href="{{ route('subir_docs.index') }}"
                    class="nav-link {{ request()->routeIs('subir_docs.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-file"></i>
                    <p>Documentación</p>
                  </a>
              @else
                  <a href="javascript:void(0)"
                    class="nav-link text-muted"
                    onclick="alertaPerfilIncompleto()">
                    <i class="nav-icon fas fa-file"></i>
                    <p>Documentación</p>
                  </a>
              @endif
            </li>
            @endif

          @if (auth()->user()->perm_client_vista_adm)
            <li class="nav-item">
              <a href="{{ route('cliente.index') }}" class="nav-link {{ request()->routeIs('cliente.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-tag"></i>
                <p> Cliente</p>
              </a>
            </li>
          @endif

          @if (auth()->user()->perm_client_vista_client)
            <li class="nav-item">
              <a href="{{ route('actualizardatoscliente.index') }}" class="nav-link {{ request()->routeIs('actualizardatoscliente.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-edit"></i>
                <p> Cliente</p>
              </a>
            </li>
          @endif
          
          <li class="nav-header">DATOS DE CONFIGURACIÓN</li>
          <li class="nav-item">
            @if (auth()->user()->grupo_configuracion)
              <a href="#" class="nav-link">
                <i class="nav-icon ti ti-settings"></i> <p>Configuración<i class="fas fa-angle-left right"></i></p>
              </a>
            @endif
            <ul class="nav nav-treeview">
              @if (auth()->user()->perm_tipo_socio_negocio)
                <li class="nav-item">
                  <a href="pages/tables/data.html" class="nav-link"><i class="ti ti-user-cog nav-icon"></i><p>Tipo socio Negocio</p></a>
                </li>  
              @endif
              @if (auth()->user()->perm_proveedor_tipo)
                <li class="nav-item">
                  <a href="{{ route('tipo_estandar.index') }}" class="nav-link {{ request()->routeIs('tipo_estandar.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tenge"></i>
                    <p>Categoria Proveedor</p>
                  </a>
                </li>  
              @endif  
              @if (auth()->user()->perm_bancos)
                <li class="nav-item">
                  <a href="{{ route('banco.index') }}" class="nav-link {{ request()->routeIs('banco.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-university"></i>
                    <p>Bancos</p>
                  </a>
                </li>  
              @endif  
       
            </ul>
          </li>
          

          
          <li class="nav-item">           
            @if (auth()->user()->grupo_utilitarios)
             <a href="#" class="nav-link activeE"> <i class="nav-icon fas fa-columns"></i> <p> Utilitarios <i class="right fas fa-angle-left"></i> </p> </a>
            @endif
            <ul class="nav nav-treeview">
               @if (auth()->user()->perm_persona)
                <li class="nav-item">
                  <a href="{{ route('persona.index') }}" class="nav-link {{ request()->routeIs('persona.*') ? 'active' : '' }}">
                    <i class="ti ti-users nav-icon"></i>
                    <p>Trabajadores</p>
                  </a>
                </li>
               @endif
              @if (auth()->user()->perm_usuario)
                <li class="nav-item">
                  <a href="{{ route('usuario.index') }}" class="nav-link {{ request()->routeIs('usuario.*') ? 'active' : '' }}">
                    <i class="ti ti-user-shield nav-icon"></i>
                    <p>Usuarios del sistema</p>
                  </a>
                </li>  
              @endif           
            </ul>
          </li>    



         <!-- <li class="nav-header">EXTRAS</li> -->


          
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

