
const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

$("#guardar_combinar_txt").on("click", function (e) { if ($(this).hasClass('send-data') == false) { $("#submit-form-combinar-txt").submit(); } else { toastr_warning('Procesando!!', 'Sea paciente se esta procesando.'); } });
$("#descargar_excel_plantilla").on("click", function (e) { if ($(this).hasClass('send-data') == false) { $("#submit-form-descargar-excel-plantilla").submit(); } else { toastr_warning('Procesando!!', 'Sea paciente se esta procesando.'); } });

// lista_select2("../ajax/ajax_general.php?op=select2EmpresaACargo", '#empresa_acargo', null);

$('#idempresa').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#idsocio_negocio').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });



// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   COMBINAR                                                 ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
function limpiar_form_combinar() {
  $('#archivo_excel').val('');
  $('#lista-archivos-seleccionados').html('');
  $('#resultado').html('');
}

document.getElementById('archivo_excel').addEventListener('change', function (event) {
  var archivos = event.target.files;
  var listaArchivos = document.getElementById('lista-archivos-seleccionados');
  listaArchivos.innerHTML = ''; // Limpiar la lista antes de agregar nuevos archivos

  // Si no hay archivos seleccionados, salir de la función
  if (archivos.length === 0) {
    listaArchivos.innerHTML = 'No se han seleccionado archivos.';
    return;
  }

  var listaHTML = '<ul>';
  for (var i = 0; i < archivos.length; i++) {
    listaHTML += '<li>' + archivos[i].name + '</li>'; // Agregar el nombre de cada archivo
  }
  listaHTML += '</ul>';

  // Mostrar los nombres de los archivos
  listaArchivos.innerHTML = listaHTML;
});


document.getElementById('btn-excel-eliminar').addEventListener('click', function () {
  document.getElementById('archivo_excel').value = ''; // Limpiar la selección de archivos
  document.getElementById('lista-archivos-seleccionados').innerHTML = ''; // Limpiar la lista mostrada
});

toastr_info('Información', 'Seleccione un mismo formato dearchivo para combinar.');

function guardar_y_editar_combinar_txt(e) {
  // e.preventDefault();

  const archivos = document.getElementById('archivo_excel').files;
  if (archivos.length === 0) {
    toastr_warning('Información', 'No se han seleccionado archivos.');
    return;
  }

  // Obtener la extensión del primer archivo
  const primeraExtension = archivos[0].name.split('.').pop().toLowerCase();

  // Validar que todos los archivos tengan la misma extensión
  for (let i = 1; i < archivos.length; i++) {
    const extension = archivos[i].name.split('.').pop().toLowerCase();
    if (extension !== primeraExtension) {
      toastr_warning('Información', 'Seleccione un mismo formato de archivo para combinar.');
      return;
    }
  }

  const fd = new FormData($('#form-combinar-txt')[0]);

  $.ajax({
    url: `${BASE_URL}/planilla-afp/import`,
    method: 'POST',
    data: fd,
    cache: false,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.status) {
        // Mostrar el listado combinado
        mostrar_lista_combinada();
        $("#guardar_combinar_txt").html('<i class="ti ti-device-floppy"></i> Conbinar').removeClass('disabled send-data');
        $('#resultado').html('<div class="alert alert-success">Cargado corecto</div>');
        $('#modal-combinar-txt').modal('hide')
      }
    },
    beforeSend: function () {
      $("#guardar_combinar_txt").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled send-data');
    },
    error: function (xhr) {
      $('#resultado').html('<div class="alert alert-danger">Error al cargar</div>');
      $("#guardar_combinar_txt").html('<i class="ti ti-device-floppy"></i> Conbinar').removeClass('disabled send-data');
    }
  });
}

// Mostrar los archivos combinados en el front-end
function mostrar_lista_combinada() {
  $('#mostrar-lista-combinada').html('<div class="p-2">Cargando...</div>');

  $.ajax({
    url: `${BASE_URL}/planilla-afp/mostrar_lista`,
    method: 'GET',
    success: function (response) {
      if (!response || response.status !== true) {
        $('#mostrar-lista-combinada').html('<div class="text-danger">Error al cargar.</div>');
        return;
      }

      const data = Array.isArray(response.data) ? response.data : [];
      if (data.length === 0) {
        $('#mostrar-lista-combinada').html('<div class="p-2">Sin resultados.</div>');
        return;
      }

      const nf = new Intl.NumberFormat('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      let html = `
        <div class="table-responsive">
          <table class="table table-sm table-striped table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:48px;">#</th>
                <th>N° Secuencia</th>
                <th>CUSPP</th>
                <th>Tipo Documento</th>
                <th>N° Documento</th>
                <th>Apellido Paterno</th>
                <th>Apellido Materno</th>
                <th>Nombres</th>
                <th>Relación Laboral</th>
                <th>Inicio R.L.</th>
                <th>Cese R.L.</th>
                <th>Excepción</th>
                <th>Rem. Asegurable</th>
                <th>Aporte c/fin</th>
                <th>Aporte s/fin</th>
                <th>Aporte empleador</th>
                <th>Rubro</th>               
                <th>AFP</th>
                <th>Archivos</th>
              </tr>
            </thead>
            <tbody>
      `;

      data.forEach((it, i) => {
        const rubroBadge =
          it.rubro_trabajador === 'N'
            ? '<span class="badge text-bg-warning">N (Máx)</span>'
            : it.rubro_trabajador === 'C'
              ? '<span class="badge text-bg-primary">C (Suma)</span>'
              : `<span class="badge text-bg-secondary">${it.rubro_trabajador ?? ''}</span>`;

        const remFmt = (it.rem_asegurable_final == null || it.rem_asegurable_final === '')
          ? ''
          : nf.format(parseFloat(it.rem_asegurable_final));

        html += `
          <tr>
            <td class="py-0 text-nowrap">${i + 1}</td>
            <td class="py-0 text-nowrap">${it.n_secuencia ?? ''}</td>
            <td class="py-0 text-nowrap">${it.cuspp ?? ''}</td>
            <td class="py-0 text-nowrap">${it.tipo_documento ?? ''}</td>
            <td class="py-0 text-nowrap">${it.n_documento ?? ''}</td>
            <td class="py-0 text-nowrap">${it.apellido_paterno ?? ''}</td>
            <td class="py-0 text-nowrap">${it.apellido_materno ?? ''}</td>
            <td class="py-0 text-nowrap">${it.nombres ?? ''}</td>
            <td class="py-0 text-nowrap">${it.relacion_laboral ?? ''}</td>
            <td class="py-0 text-nowrap">${it.inicio_relacion_laboral ?? ''}</td>
            <td class="py-0 text-nowrap">${it.cese_relacion_laboral ?? ''}</td>
            <td class="py-0 text-nowrap">${it.excepcion_de_aportar ?? ''}</td>
            <td class="py-0 text-nowrap text-end">${remFmt}</td>
            <td class="py-0 text-nowrap">${it.aporte_con_fin ?? ''}</td>
            <td class="py-0 text-nowrap">${it.aporte_sin_fin ?? ''}</td>
            <td class="py-0 text-nowrap">${it.aporte_empleador ?? ''}</td>
            <td class="py-0 text-nowrap">${rubroBadge}</td>
            
            <td class="py-0 text-nowrap">${it.afp ?? ''}</td>
            <td class="py-0 text-nowrap">${it.archivos ?? ''}</td>
          </tr>
        `;
      });

      html += `
            </tbody>
          </table>
        </div>
      `;

      $('#mostrar-lista-combinada').html(html);
    },
    error: function () {
      $('#mostrar-lista-combinada').html('<div class="text-danger">No se pudo cargar la lista.</div>');
    }
  });
}




// Descargar el archivo combinado en formato .txt o Excel
function descargar_combinado(formato) {
  window.location.href = `${BASE_URL}/planilla-afp/descargar/excel`;
}

mostrar_lista_combinada();

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       J Q   F O R M   V A L I D A T I O N S                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
$(function () {

  // validamos el formulario  

  $('#fecha_pago_obrero').on('change', function () { $(this).trigger('blur'); });
  $('#fecha_valorizacion').on('change', function () { $(this).trigger('blur'); });
  $('#empresa_acargo').on('change', function () { $(this).trigger('blur'); });

  $("#form-combinar-txt").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {

      archivo_excel: { required: true, extension: "rem|snl|toc|jor|txt" }
    },
    messages: {

      archivo_excel: { required: "Campo requerido.", extension: "Solo archivos {0}." }
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
      guardar_y_editar_combinar_txt(e);
    },
  });

  $('#fecha_pago_obrero').rules('add', { required: true, messages: { required: "Campo requerido" } });
  $('#fecha_valorizacion').rules('add', { required: true, messages: { required: "Campo requerido" } });
  $('#empresa_acargo').rules('add', { required: true, messages: { required: "Campo requerido" } });



});