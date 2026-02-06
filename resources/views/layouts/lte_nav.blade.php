<style>
/* Item */
.notification-item {
  max-width: 100%;
  align-items: flex-start;
}

/* Ícono */
.notification-icon {
  margin-right: 8px;
  margin-top: 3px;
  flex-shrink: 0;
}

/* Contenido */
.notification-content {
  width: 100%;
  min-width: 0;
}

/* Título */
.notification-title {
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Fila proveedor / periodo */
.notification-row {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #6c757d;
  margin-top: 2px;
}

/* Proveedor */
.notification-proveedor {
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Periodo alineado a la derecha */
.notification-periodo {
  white-space: nowrap;
  margin-left: 8px;
}

/* Línea divisoria */
.notification-divider {
  border-top: 1px solid #e5e7eb;
  margin: 4px 0;
}

/* Tiempo */
.notification-time {
  font-size: 13px;
  color: #9aa0a6;
  text-align: left;
}


</style>
<!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light text-white bg-color-principal">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars text-white"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link"><i class="ti ti-home text-white" ></i></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown hidden">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments text-white" ></i>
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
      <!--<li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell text-white"></i>
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
      </li>-->
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell text-white"></i>

          @php
            $unreadCount = auth()->user()->unreadNotifications->count();
          @endphp

          @if($unreadCount > 0)
            <span class="badge badge-warning navbar-badge">
              {{ $unreadCount }}
            </span>
          @endif
        </a>

        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

          <span class="dropdown-item dropdown-header">
            {{ $unreadCount }} Notificaciones
          </span>

          <div class="dropdown-divider"></div>

          @forelse(auth()->user()->unreadNotifications->take(5) as $n)
            <a href="{{ route('notificaciones.leer', $n->id) }}"
              class="dropdown-item notification-item d-flex">

              <!-- Contenido -->
              <div class="notification-content">

                <!-- Título -->
                <div class="notification-title">
                {{$n->data['mensaje']}}
                </div>

                <!-- Proveedor + Periodo en 2 columnas -->
                <div class="notification-row">
                  <span class="notification-proveedor">
                    {{ $n->data['Proveedor'] ?? 'Proveedor' }}
                  </span>                 
                </div>
                <!-- Tiempo -->
                <div class="notification-time">
                   Periodo - {{ $n->data['periodo'] ?? '2026' }}
                </div>
                 <span class="float-right text-muted text-sm">{{ $n->created_at->diffForHumans() }}</span>

              </div>
            </a>


            <div class="dropdown-divider"></div>
          @empty
            <span class="dropdown-item text-muted text-center">
              No tienes notificaciones
            </span>
            <div class="dropdown-divider"></div>
          @endforelse

          <a href="{{ route('notificaciones.marcarTodas') }}"
            class="dropdown-item dropdown-footer text-center">
            Marcar todas como leídas
          </a>

        </div>
      </li>


      {{-- Full Scren --}}
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt text-white"></i>
        </a>
      </li>

      <!-- Datos de usuario -->
      <li class="nav-item dropdown ">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <img src="../dist/docs/all_trabajador/perfil/" class="user-image img-circle" alt="User Image" width="30" onerror="this.src='{{ asset('adminlte3/dist/svg/user_default.svg') }}';"> 
              
          <span class="hidden-xs d-none show-min-width-1200px text-white text-bold" style="font-size: small;" >{{ auth()->user()->persona?->nombre_razonsocial }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <!-- Widget: user widget style 1 -->
          <div class="card card-widget widget-user mb-0">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class="widget-user-header" style="background-color: #231f20">
              <h3 class="widget-user-username text-white  text-bold" style="font-size: larger;">{{ auth()->user()->persona?->nombre_razonsocial }}</h3>
              <h5 class="widget-user-desc text-white" style="font-size: larger;">{{ Auth::user()->name }}</h5>
            </div>
            <div class="widget-user-image">
              <img class="img-circle elevation-2" src="../dist/docs/all_trabajador/perfil/" alt="User Avatar" onerror="this.src='{{ asset('adminlte3/dist/svg/user_default.svg') }}';" />
            </div>
            <div class="card-body pt-5">
              <span class="dropdown-item dropdown-header">Info personal</span>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="fas fa-address-card"></i>
               Doc: {{ auth()->user()->persona?->numero_documento }}        
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item">
                <i class="fas fa-phone-alt"></i> Tel: {{ auth()->user()->persona?->celular }}
              </a>
              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item w-280px recorte-text">
                <i class="fas fa-user"></i> Usuario: {{ Auth::user()->email }}
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
        <a class="nav-link" role="button">
          <i class="fas fa-th-large text-white"></i>
        </a>
      </li>


    </ul>
  </nav>
  <!-- /.navbar -->