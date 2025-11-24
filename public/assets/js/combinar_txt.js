
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
  $('#archivo_txt').val('');
  $('#lista-archivos-seleccionados').html('');
  $('#resultado').html('');
}

document.getElementById('archivo_txt').addEventListener('change', function (event) {
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
  document.getElementById('archivo_txt').value = ''; // Limpiar la selección de archivos
  document.getElementById('lista-archivos-seleccionados').innerHTML = ''; // Limpiar la lista mostrada
});

toastr_info('Información', 'Seleccione un mismo formato dearchivo para combinar.');

function guardar_y_editar_combinar_txt(e) {
  // e.preventDefault();

  const archivos = document.getElementById('archivo_txt').files;
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
    url: `${BASE_URL}/combinar-txt/guardar_txt`,
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
  $.ajax({
    url: `${BASE_URL}/combinar-txt/mostrar_lista`,
    method: 'GET',
    success: function (response) {
      let contenido = '<ul>';

      response.forEach(item => {
        // Mostrar solo las columnas que existen en la respuesta
        contenido += `
          <li>
            ${item.codalterno}|${item.dni}|${item.codbase}|${item.monto1 !== undefined ? parseFloat(item.monto1) + '|' : ''}${item.monto2 !== undefined ? ( item.monto2 == null ? '|' : parseFloat(item.monto2) + '|' ): ''}${item.monto3 !== undefined ? parseFloat(item.monto3) + '|' : ''} ---> Archivos: ${item.archivos}
          </li>
        `;
      });

      contenido += '</ul>';
      $('#mostrar-lista-combinada').html(contenido);
    }
  });
}


// Descargar el archivo combinado en formato .txt o Excel
function descargar_combinado(formato) {
  window.location.href = `${BASE_URL}/combinar-txt/descargar/${formato}`;
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

      archivo_txt: { required: true, extension: "rem|snl|toc|jor|txt" }
    },
    messages: {

      archivo_txt: { required: "Campo requerido.", extension: "Solo archivos {0}." }
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