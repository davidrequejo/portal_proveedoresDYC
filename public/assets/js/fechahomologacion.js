const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

$("#guardar_registro_fecha_homologacion").on("click", function () {
  $("#submit-form-fecha-homologacion").submit();
});

function show_hide_escenario(flag) {
  if (flag == 1) {
    $('#div-tabla-principal-fechahomologacion').show();
    $(".btn-agregar-fechahomologacion").show();
    $(".btn-cancelar").hide();
  } else if (flag == 2) {
    $('#div-tabla-principal-fechahomologacion').hide();
    $(".btn-agregar-fechahomologacion").hide();
    $(".btn-cancelar").show();
  }
}

// ═════════════════════════════════════════════════════════════════════════════
// TABLA PRINCIPAL
// ═════════════════════════════════════════════════════════════════════════════

const state = {
  page: 1,
  per_page: 10,
  sort: 'descripcion',
  dir: 'asc',
  q: ''
};

function tabla_principal_cargar_fecha_homologacion() {

  $.getJSON(`${BASE_URL}/fechahomologacion/tabla_principal`, state, function (res) {
    renderFilas(res.data);
    renderPaginacion(res.current_page, res.last_page);
    marcarOrden(state.sort, state.dir);
  }).fail(function (xhr) {
    ver_errores(xhr);
  });
}

function renderFilas(rows) {
  const $tb = $("#tabla-fechahomologacion tbody").empty();

  if (!rows || rows.length === 0) {
    $tb.append('<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>');
    return;
  }

  rows.forEach(r => {

    let estado = r.estado_trash == '1'
      ? '<span class="badge badge-success">Activo</span>'
      : '<span class="badge badge-danger">Inactivo</span>';

    $tb.append(`
      <tr class="fila-fechahomologacion" data-id="${r.idfecha_homologacion}">
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning" onclick="ver_editar_fecha_homologacion(${r.idfecha_homologacion})">
              <i class="ti ti-edit"></i>
            </button>
            <button class="btn btn-danger" onclick="eliminar_fecha_homologacion(${r.idfecha_homologacion}, '${r.descripcion}')">
              <i class="ti ti-trash"></i>
            </button>
          </div>
        </td>
        <td class="text-center">${String(r.idfecha_homologacion).padStart(3, '0')}</td>
        <td>${r.descripcion}</td>
        <td>${r.fecha_inicio}</td>
        <td>${r.fecha_fin}</td>
        <td>${estado}</td>
      </tr>
    `);
  });
}

function renderPaginacion(actual, total) {
  const $p = $("#paginacion").empty();

  const mk = (l, p, d = false, a = false) =>
    `<li class="page-item ${d ? 'disabled' : ''} ${a ? 'active' : ''}">
      <a class="page-link" href="#" data-page="${p}">${l}</a>
     </li>`;

  $p.append(mk('Ant.', actual - 1, actual <= 1));

  let win = 2;
  let ini = Math.max(1, actual - win);
  let fin = Math.min(total, actual + win);

  if (ini > 1) {
    $p.append(mk('1', 1));
    if (ini > 2) $p.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
  }

  for (let i = ini; i <= fin; i++) {
    $p.append(mk(i, i, false, i === actual));
  }

  if (fin < total) {
    if (fin < total - 1) $p.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
    $p.append(mk(total, total));
  }

  $p.append(mk('Sig.', actual + 1, actual >= total));
}

function marcarOrden(col, dir) {
  $("#tabla-fechahomologacion thead th.sortable").each(function () {
    const $th = $(this);
    $th.removeClass('asc desc');
    if ($th.data('sort') === col) $th.addClass(dir);
  });
}

$("#paginacion").on("click", "a.page-link", function (e) {
  e.preventDefault();
  const page = parseInt($(this).data("page"));
  if (!isNaN(page)) {
    state.page = Math.max(1, page);
    tabla_principal_cargar_fecha_homologacion();
  }
});

$("#tabla-fechahomologacion thead").on("click", "th.sortable", function () {
  const col = $(this).data("sort");
  state.dir = (state.sort === col && state.dir === 'asc') ? 'desc' : 'asc';
  state.sort = col;
  state.page = 1;
  tabla_principal_cargar_fecha_homologacion();
});

let t = null;
$("#buscar").on("input", function () {
  clearTimeout(t);
  t = setTimeout(() => {
    state.q = $(this).val();
    state.page = 1;
    tabla_principal_cargar_fecha_homologacion();
  }, 300);
});

$("#perPage").on("change", function () {
  state.per_page = parseInt($(this).val()) || 10;
  state.page = 1;
  tabla_principal_cargar_fecha_homologacion();
});

// Carga inicial
tabla_principal_cargar_fecha_homologacion();

// ═════════════════════════════════════════════════════════════════════════════
// CRUD
// ═════════════════════════════════════════════════════════════════════════════

function limpiar_form_fecha_homologacion() {
  $("#idfecha_homologacion").val('');
  $("#descripcion").val('');
  $("#fecha_inicio_proceso").val('');
  $(".form-control").removeClass('is-valid is-invalid');
  $(".error.invalid-feedback").remove();
}

function ver_editar_fecha_homologacion(id) {

  limpiar_form_fecha_homologacion();
  $('#modal-agregar-fechahomologacion').modal('show');

  $.getJSON(`${BASE_URL}/fechahomologacion/${id}/ver-editar`, function (e) {
    if (e.status === true) {
      $("#idfecha_homologacion").val(e.data.idfecha_homologacion);
      $("#descripcion").val(e.data.descripcion);
      $("#fecha_inicio_proceso").val(e.data.fecha_inicio_proceso);
    } else {
      toastr.error('Registro no encontrado');
    }
  }).fail(ver_errores);
}

function guardar_y_editar_fecha_homologacion() {

  let formData = new FormData($("#form-agregar-fechahomologacion")[0]);
  let id = $("#idfecha_homologacion").val();
  let url = id === ''
    ? `${BASE_URL}/fechahomologacion/crear_fecha_homologacion`
    : `${BASE_URL}/fechahomologacion/editar_fecha_homologacion/${id}`;

  if (id !== '') formData.append('_method', 'PUT');

  $.ajax({
    url,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (e) {
      if (e.status === true) {
        tabla_principal_cargar_fecha_homologacion();
        limpiar_form_fecha_homologacion();
        Swal.fire("Correcto!", "Registro guardado correctamente", "success");
        $("#modal-agregar-fechahomologacion").modal("hide");
      } else {
        ver_errores(e);
      }
    },
    error: ver_errores
  });
}

function eliminar_fecha_homologacion(id, descripcion) {

  Swal.fire({
    title: "¿Eliminar registro?",
    html: `<b class="text-danger">${descripcion}</b>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar"
  }).then((r) => {

    if (r.isConfirmed) {
      $.ajax({
        url: `${BASE_URL}/fechahomologacion/eliminar_fecha_homologacion/${id}`,
        type: "PUT",
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (e) {
          if (e.status === true) {
            Swal.fire("Eliminado!", "Registro eliminado correctamente", "success");
            tabla_principal_cargar_fecha_homologacion();
          }
        }
      });
    }
  });
}

// ═════════════════════════════════════════════════════════════════════════════
// VALIDACIONES
// ═════════════════════════════════════════════════════════════════════════════

$(function () {

  $("#form-agregar-fechahomologacion").validate({
    rules: {
      descripcion:   { required: true },
      fecha_inicio_proceso:  { required: true },
    },
    messages: {
      descripcion:  { required: "Campo requerido" },
      fecha_inicio_proceso: { required: "Campo requerido" },
    },
        errorElement: "span",

    errorPlacement: function (error, element) {
      error.addClass("invalid-feedback");
      element.closest(".form-group").append(error);
    },

    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },

    unhighlight: function (element, errorClass, validClass) {
      $(element).removeClass("is-invalid").addClass("is-valid");
    },

    submitHandler: function (e) {
      $(".modal-body").animate({ scrollTop: $(document).height() }, 600); // Scrollea hasta abajo de la página
      guardar_y_editar_fecha_homologacion(e);
    },

  });

});
