<!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link"><i class="ti ti-home"></i></a>
      </li>
      <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Catalogo</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
          <li><a href="#" class="dropdown-item"><i class="ti ti-keyframes"></i> Recursos </a></li>
          <li><a href="#" class="dropdown-item"><i class="ti ti-buildings"></i> Proyecto</a></li>

          <li class="dropdown-divider"></li>

          <!-- Level two dropdown-->
          <li class="dropdown-submenu dropdown-hover">
            <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Otros</a>
            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
              <li>
                <a tabindex="-1" href="#" class="dropdown-item">level 2</a>
              </li>

              <!-- Level three dropdown-->
              <li class="dropdown-submenu">
                <a id="dropdownSubMenu3" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">level 2</a>
                <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                  <li><a href="#" class="dropdown-item">3rd level</a></li>
                  <li><a href="#" class="dropdown-item">3rd level</a></li>
                </ul>
              </li>
              <!-- End Level three -->

              <li><a href="#" class="dropdown-item">level 2</a></li>
              <li><a href="#" class="dropdown-item">level 2</a></li>
            </ul>
          </li>
          <!-- End Level two -->
        </ul>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="{{ asset('adminlte3/dist/img/user1-128x128.jpg') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="{{ asset('adminlte3/dist/img/user8-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="{{ asset('adminlte3/dist/img/user3-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>

      {{-- Full Scren --}}
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>

      <!-- Datos de usuario -->
      <li class="nav-item dropdown ">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <img src="../dist/docs/all_trabajador/perfil/" class="user-image img-circle" alt="User Image" width="30" onerror="this.src='{{ asset('adminlte3/dist/svg/user_default.svg') }}';"> 
              
          <span class="hidden-xs d-none show-min-width-1200px">{{ Auth::user()->name }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <!-- Widget: user widget style 1 -->
          <div class="card card-widget widget-user mb-0">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header bg-info">
              <h3 class="widget-user-username">{{ Auth::user()->name }}</h3>
              <h5 class="widget-user-desc">ADM</h5>
            </div>
            <div class="widget-user-image">
              <img class="img-circle elevation-2" src="../dist/docs/all_trabajador/perfil/" alt="User Avatar" onerror="this.src='{{ asset('adminlte3/dist/svg/user_default.svg') }}';" />
            </div>
            <div class="card-body pt-5">
              <span class="dropdown-item dropdown-header">Info personal</span>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="fas fa-address-card"></i>
               DNI: 000000000000         
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="fas fa-phone-alt"></i> Telefono: 999999999999999
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item w-280px recorte-text">
                <i class="fas fa-envelope"></i> Email: {{ Auth::user()->email }}
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item dropdown-footer"> <small>Más informacion cominicarse con el administrador </small>  </a>
              <!-- /.row -->
              <div class="dropdown-item">
                <form method="POST" action="{{ route('logout') }}" x-data>
                  @csrf
                  {{-- <a href="{{ route('logout') }}" class="btn btn-danger btn-block" @click.prevent="$root.submit();" type="submit" >Cerrar sesion</a> --}}
                  {{-- <input type="submit" class="btn btn-danger btn-block" value="Cerrar sesion" > --}}
                  <button type="submit" class="btn btn-danger btn-block">  Cerrar sesión</button>
                </form>
              </div>
              
            </div>
            <div class="card-footer py-1 text-center ">
              <a class="hove-negrita" href="javascript:void(0)" onclick="abrir_calculadora()"><i class="fas fa-calculator"></i> Abrir calculadora</a>
            </div>
          </div>
          <!-- /.widget-user -->
          
        </div>
      </li>

      {{-- Config --}}
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>


    </ul>
  </nav>
  <!-- /.navbar -->