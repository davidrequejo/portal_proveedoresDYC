<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="app-url" content="{{ url('/') }}">
  
  <title>Plantillas | Portal Proveedores D&C</title>

  <link rel="icon" href="{{ asset('assets/images/brand-logos/dc-logo_cirsulo.png') }}" type="image/png">

  @include('layouts.lte_head')

  <style>
    /* Estilos para la tabla */
    #tabla-plantillas_filter {
      width: calc(100% - 10px) !important;
      display: flex !important;
      justify-content: space-between !important;
    }
    #tabla-plantillas_filter label {
      width: 100% !important;
    }
    #tabla-plantillas_filter label input {
      width: 100% !important;
    }

    /* Indicadores de orden (opcional) */
    th.sortable {
      cursor: pointer;
      position: relative;
    }
    th.sortable.asc::after {
      content: "▲";
      font-size: .7rem;
      position: absolute;
      right: .4rem;
    }
    th.sortable.desc::after {
      content: "▼";
      font-size: .7rem;
      position: absolute;
      right: .4rem;
    }

    .fila-plantilla.selected {
      background-color: #e7f1ff !important;
    }

    /* Badges para estado */
    .badge-activo {
      background-color: #28a745;
      color: white;
      padding: 0.3rem 0.6rem;
      border-radius: 20px;
      font-size: 0.75rem;
    }
    .badge-inactivo {
      background-color: #dc3545;
      color: white;
      padding: 0.3rem 0.6rem;
      border-radius: 20px;
      font-size: 0.75rem;
    }
  </style>

</head>
<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed pace-orange">
  <div class="wrapper">

    <!-- Preloader -->
    @include('layouts.lte_preloader')

    <!-- Navbar -->
    @include('layouts.lte_nav')
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    @include('layouts.lte_aside')

    @if (auth()->user()->perm_plantillas) {{-- Asumiendo que tienes un permiso específico --}}
      <!-- Content Wrapper -->
      <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0">Plantillas de Evaluación de Proveedores</h1>
              </div>
              <div class="col-sm-6">
                <div class="float-right">
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-plantilla" onclick="limpiarFormPlantilla()">
                      <i class="ti ti-file-plus"></i> Crear nueva plantilla
                    </button>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      <a class="dropdown-item" href="#"><i class="ti ti-file-upload"></i> Importar plantilla</a>
                      <div class="dropdown-divider my-0"></div>
                      <a class="dropdown-item" href="#"><i class="ti ti-file-export"></i> Exportar todo</a>
                    </div>
                  </div>
                  <button type="button" class="btn btn-danger btn-cancelar" onclick="cancelarEdicion()" style="display: none;">
                    <i class="ri-arrow-left-line"></i> Regresar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main content -->
        <section class="content">
          <div class="container-fluid">
            <div class="row">
              <div class="col" id="div-tabla-principal-plantillas">
                <div class="card">
                  <div class="card-body pb-1">
                    <!-- Filtros -->
                    <div class="row mb-2">
                      <div class="col-md-4">
                        <input type="search" id="buscar" class="form-control form-control-sm" placeholder="Buscar por nombre...">
                      </div>
                      <div class="col-md-2">
                        <select id="filtro-tipo" class="form-control form-control-sm">
                          <option value="">Todos los tipos</option>
                          <option value="SELECCION">Selección</option>
                          <option value="EVALUACION">Evaluación</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <select id="filtro-estado" class="form-control form-control-sm">
                          <option value="">Todos los estados</option>
                          <option value="1">Activas</option>
                          <option value="0">Inactivas</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <select id="perPage" class="form-control form-control-sm">
                          <option value="5">5</option>
                          <option value="10" selected>10</option>
                          <option value="25">25</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-outline-info btn-block recargar-tabla" data-toggle="tooltip" title="Recargar">
                          <i class="ti ti-refresh"></i> Recargar
                        </button>
                      </div>
                    </div>

                    <!-- Tabla de plantillas -->
                    <div class="table-responsive">
                      <table class="table table-bordered table-hover table-sm" id="tabla-plantillas">
                        <thead>
                          <tr>
                            <th class="sortable" data-columna="id">ID</th>
                            <th class="sortable" data-columna="nombre">Nombre</th>
                            <th class="sortable" data-columna="tipo">Tipo</th>
                            <th class="sortable" data-columna="activa">Estado</th>
                            <th class="sortable" data-columna="fecha_creacion">Fecha creación</th>
                            <th>Acciones</th>
                          </tr>
                        </thead>
                        <tbody id="cuerpo-tabla">
                          <!-- Se llenará vía AJAX -->
                          <tr>
                            <td colspan="6" class="text-center">Cargando...</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <!-- Footer con paginación -->
                  <div class="card-footer clearfix bg-color-white">
                    <ul class="pagination pagination-sm m-0 float-right" id="paginacion">
                      <!-- Generado por JS -->
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    @else
      @include('componentes_erp.no-permiso')
    @endif

    @include('layouts.lte_footer')

    <!-- Modal para crear/editar plantilla -->
    <div class="modal fade" id="modal-plantilla" tabindex="-1" role="dialog" aria-labelledby="modalPlantillaLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header py-2 bg-color-principal">
            <h5 class="modal-title text-white" id="modalPlantillaLabel">Nueva Plantilla</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span class="text-danger" aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="form-plantilla" method="POST">
              @csrf
              <input type="hidden" name="id" id="plantilla_id">

              <div class="form-group">
                <label for="nombre">Nombre de la plantilla <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
              </div>

              <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
              </div>

              <div class="form-group">
                <label for="tipo">Tipo <span class="text-danger">*</span></label>
                <select class="form-control" id="tipo" name="tipo" required>
                  <option value="">Seleccione</option>
                  <option value="SELECCION">Selección (proveedor nuevo)</option>
                  <option value="EVALUACION">Evaluación (reevaluación)</option>
                </select>
              </div>

              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="activa" name="activa" value="1" checked>
                  <label class="custom-control-label" for="activa">Plantilla activa</label>
                </div>
              </div>

              <div class="progress" id="barra_progress" style="display: none;">
                <div id="barra_progress_bar" class="progress-bar" role="progressbar" style="width: 0%;">0%</div>
              </div>
            </form>
          </div>
          <div class="modal-footer justify-content-between py-1">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
              <i class="ti ti-circle-dashed-x"></i> Cerrar
            </button>
            <button type="button" class="btn btn-primary" id="guardar_plantilla">
              <i class="ti ti-device-floppy"></i> Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark"></aside>
  </div>

  @include('layouts.lte_script')

  <script>
    $(function() {
      $('[data-toggle="tooltip"]').tooltip();

      // Variables globales
      let currentPage = 1;
      let currentSort = { columna: 'id', direccion: 'desc' };
      let filters = {
        buscar: '',
        tipo: '',
        estado: ''
      };

      // Cargar datos iniciales
      cargarPlantillas();

      // Eventos de filtros y paginación
      $('#buscar').on('keyup', debounce(function() {
        filters.buscar = $(this).val();
        currentPage = 1;
        cargarPlantillas();
      }, 300));

      $('#filtro-tipo, #filtro-estado').on('change', function() {
        filters.tipo = $('#filtro-tipo').val();
        filters.estado = $('#filtro-estado').val();
        currentPage = 1;
        cargarPlantillas();
      });

      $('#perPage').on('change', function() {
        currentPage = 1;
        cargarPlantillas();
      });

      $('.recargar-tabla').on('click', function() {
        cargarPlantillas();
      });

      // Ordenamiento
      $(document).on('click', '.sortable', function() {
        let columna = $(this).data('columna');
        if (currentSort.columna === columna) {
          currentSort.direccion = currentSort.direccion === 'asc' ? 'desc' : 'asc';
        } else {
          currentSort.columna = columna;
          currentSort.direccion = 'asc';
        }
        $('.sortable').removeClass('asc desc');
        $(this).addClass(currentSort.direccion);
        cargarPlantillas();
      });

      // Guardar plantilla
      $('#guardar_plantilla').on('click', function() {
        guardarPlantilla();
      });

      // Editar (al hacer clic en botón editar)
      $(document).on('click', '.btn-editar', function() {
        let id = $(this).data('id');
        editarPlantilla(id);
      });

      // Eliminar (con confirmación)
      $(document).on('click', '.btn-eliminar', function() {
        let id = $(this).data('id');
        let nombre = $(this).data('nombre');
        eliminarPlantilla(id, nombre);
      });

      // Función para cargar plantillas vía AJAX
      function cargarPlantillas() {
        let perPage = $('#perPage').val();
        let data = {
          page: currentPage,
          perPage: perPage,
          sort_col: currentSort.columna,
          sort_dir: currentSort.direccion,
          buscar: filters.buscar,
          tipo: filters.tipo,
          estado: filters.estado
        };

        $.ajax({
          url: '{{ route("plantillas.index") }}', // Ajusta la ruta
          type: 'GET',
          data: data,
          beforeSend: function() {
            $('#cuerpo-tabla').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-pulse"></i> Cargando...</td></tr>');
          },
          success: function(response) {
            renderTabla(response.data);
            renderPaginacion(response);
          },
          error: function() {
            $('#cuerpo-tabla').html('<tr><td colspan="6" class="text-center text-danger">Error al cargar datos</td></tr>');
          }
        });
      }

      function renderTabla(plantillas) {
        let html = '';
        if (plantillas.length === 0) {
          html = '<tr><td colspan="6" class="text-center">No hay plantillas registradas</td></tr>';
        } else {
          plantillas.forEach(p => {
            let estadoBadge = p.activa ? '<span class="badge-activo">Activa</span>' : '<span class="badge-inactivo">Inactiva</span>';
            let tipoTexto = p.tipo === 'SELECCION' ? 'Selección' : 'Evaluación';
            let fecha = p.fecha_creacion ? new Date(p.fecha_creacion).toLocaleDateString('es-PE') : '';
            html += `<tr>
              <td>${p.id}</td>
              <td>${escapeHtml(p.nombre)}</td>
              <td>${tipoTexto}</td>
              <td>${estadoBadge}</td>
              <td>${fecha}</td>
              <td>
                <button class="btn btn-xs btn-info btn-editar" data-id="${p.id}" title="Editar"><i class="ti ti-edit"></i></button>
                <button class="btn btn-xs btn-danger btn-eliminar" data-id="${p.id}" data-nombre="${escapeHtml(p.nombre)}" title="Eliminar"><i class="ti ti-trash"></i></button>
                <button class="btn btn-xs btn-secondary btn-configurar" data-id="${p.id}" title="Configurar factores"><i class="ti ti-settings"></i></button>
              </td>
            </tr>`;
          });
        }
        $('#cuerpo-tabla').html(html);
      }

      function renderPaginacion(data) {
        let { current_page, last_page, from, to, total } = data;
        let html = '';
        if (last_page > 1) {
          // Botón anterior
          html += `<li class="page-item ${current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current_page - 1}">&laquo;</a>
          </li>`;

          // Páginas
          for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
              html += `<li class="page-item ${i === current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
              </li>`;
            } else if (i === current_page - 3 || i === current_page + 3) {
              html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
          }

          // Botón siguiente
          html += `<li class="page-item ${current_page === last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${current_page + 1}">&raquo;</a>
          </li>`;
        }
        $('#paginacion').html(html);

        // Evento de clic en paginación
        $('#paginacion .page-link').on('click', function(e) {
          e.preventDefault();
          let page = $(this).data('page');
          if (page && page !== currentPage) {
            currentPage = page;
            cargarPlantillas();
          }
        });
      }

      // Función para guardar (crear/actualizar)
      function guardarPlantilla() {
        let form = $('#form-plantilla');
        let url = $('#plantilla_id').val() ? '{{ route("plantillas.update") }}' : '{{ route("plantillas.store") }}';
        let method = $('#plantilla_id').val() ? 'PUT' : 'POST';

        $.ajax({
          url: url,
          type: method,
          data: form.serialize(),
          beforeSend: function() {
            $('#barra_progress').show();
            $('#barra_progress_bar').css('width', '50%').text('Guardando...');
          },
          success: function(response) {
            $('#barra_progress_bar').css('width', '100%').text('¡Completado!');
            setTimeout(() => {
              $('#modal-plantilla').modal('hide');
              cargarPlantillas();
            }, 500);
          },
          error: function(xhr) {
            let errors = xhr.responseJSON?.errors;
            let mensaje = 'Error al guardar';
            if (errors) {
              mensaje = Object.values(errors).flat().join('<br>');
            }
            alert(mensaje);
          },
          complete: function() {
            setTimeout(() => {
              $('#barra_progress').hide();
              $('#barra_progress_bar').css('width', '0%').text('0%');
            }, 1000);
          }
        });
      }

      // Cargar datos para edición
      function editarPlantilla(id) {
        $.get('{{ route("plantillas.show") }}', { id: id }, function(data) {
          $('#plantilla_id').val(data.id);
          $('#nombre').val(data.nombre);
          $('#descripcion').val(data.descripcion);
          $('#tipo').val(data.tipo);
          $('#activa').prop('checked', data.activa == 1);
          $('#modalPlantillaLabel').text('Editar Plantilla');
          $('#modal-plantilla').modal('show');
        });
      }

      function eliminarPlantilla(id, nombre) {
        if (confirm(`¿Está seguro de eliminar la plantilla "${nombre}"?`)) {
          $.ajax({
            url: '{{ route("plantillas.destroy") }}',
            type: 'DELETE',
            data: { id: id, _token: '{{ csrf_token() }}' },
            success: function() {
              cargarPlantillas();
            },
            error: function() {
              alert('No se pudo eliminar');
            }
          });
        }
      }

      function limpiarFormPlantilla() {
        $('#form-plantilla')[0].reset();
        $('#plantilla_id').val('');
        $('#modalPlantillaLabel').text('Nueva Plantilla');
        $('#activa').prop('checked', true);
      }

      function cancelarEdicion() {
        // Si implementas vista de detalle, etc.
      }

      // Utilidades
      function debounce(func, wait) {
        let timeout;
        return function() {
          clearTimeout(timeout);
          timeout = setTimeout(() => func.apply(this, arguments), wait);
        };
      }

      function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>"]/g, function(match) {
          if (match === '&') return '&amp;';
          if (match === '<') return '&lt;';
          if (match === '>') return '&gt;';
          if (match === '"') return '&quot;';
          return match;
        });
      }
    });
  </script>

</body>
</html>