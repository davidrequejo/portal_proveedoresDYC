// $('#arbol-proyecto').jstree({
//   'core': {
//     'data': [
//       {
//         "text": "ESCRITORIO", 
//         "state": { "opened": true },  // abierto
//         "children": [
//           { "text": "PROYECTO FLORES", "icon": "ti ti-buildings text-primary", },
//           { "text": "PROYECTO ANDINA", "icon": "ti ti-buildings text-primary", },
//           { "text": "PROYECTO ANDINA", "icon": "ti ti-buildings text-primary", },
//           { "text": "PROYECTO ANDINA", "icon": "ti ti-buildings text-primary", },
//           { "text": "PROYECTO ANDINA", "icon": "ti ti-buildings text-primary", },
//           { "text": "PROYECTO ANDINA", "icon": "ti ti-buildings text-primary", }
//         ]
//       },
//       {
//         "text": "Terminados", "icon": "ri-folder-check-fill text-danger",
//         "state": { "opened": false }, // cerrado
//         "children": [
//           { "text": "PROYECTO KELL", "icon": "ti ti-buildings text-primary" },
//           { "text": "PROYETO NORMADIA", "icon": "ti ti-buildings text-primary" }
//         ]
//       },
//       {
//         "text": "Papelera", "icon": "ri-folder-forbid-fill text-danger",
//         "state": { "opened": false }, // cerrado
//         "children": [
//           { "text": "PROYECTO ATURIO", "icon": "ti ti-buildings text-primary" },
//           { "text": "PROYECTO ROCHILL", "icon": "ti ti-buildings text-primary" }
//         ]
//       }
//     ]
//   }
// });

const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

$("#guardar_registro_pg_grupo").on("click", function (e) { if ($(this).hasClass('send-data') == false) { $("#submit-form-pg-grupo").submit(); } else { toastr_warning('Procesando!!', 'Sea paciente se esta procesando.'); } });
$("#guardar_registro_presupuesto").on("click", function (e) { if ($(this).hasClass('send-data') == false) { $("#submit-form-presupuesto").submit(); } else { toastr_warning('Procesando!!', 'Sea paciente se esta procesando.'); } });


$('#pg_icono').select2({ templateResult: templateIcono, templateSelection: templateIcono, theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#pg_icono_color').select2({ templateResult: templateIcono, templateSelection: templateIcono, theme: "bootstrap4", placeholder: "Selecione", allowClear: true });

$('#p_idproyecto').select2({  theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#p_icono_color').select2({ templateResult: templateIcono, templateSelection: templateIcono, theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#p_icono_color').select2({ templateResult: templateIcono, templateSelection: templateIcono, theme: "bootstrap4", placeholder: "Selecione", allowClear: true });



function templateIcono(state) {
  //console.log(state);
  if (!state.id) { return state.text; }
  var icono = state.title != '' ? state.title : '';
  var $state = $(`<span><i class="${icono}" ></i> ${state.text}</span>`);
  return $state;
}



// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   G R U P O   P R E S U P U E S T O                                                         ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Devuelve el texto del nodo normalizado
function getNodeText(node, { trim = true, upper = true } = {}) {
  let txt = String(node?.text ?? '');
  if (trim)  txt = txt.trim();
  if (upper) txt = txt.toUpperCase();
  return txt;
}


const $arbol = $('#arbol-proyecto');

// Helpers id
function isGrupo(node) { return String(node.id).startsWith('g_'); }
function isPresu(node) { return String(node.id).startsWith('p_'); }
function tree_id(node) { return parseInt(String(node.id).split('_')[1]); }

let ARBOL_INICIALIZADO = false;

function listar_tree_grupo() {
  const tree = $arbol.jstree(true);

  // Si ya está inicializado, hacemos SOLO un reload global (sin recargar la página)
  if (tree) {
    tree.refresh(); // 🔁 Recarga general
    return;
  }

  // Inicialización usando AJAX dentro de jsTree (sin $.getJSON)
  $arbol.jstree({
    core: {
      // themes: { stripes: true },
      check_callback: true,
      data: {
        url: `${BASE_URL}/arbol-presupuestos/jstree`,
        method: 'GET',
        dataType: 'json',
        data: (node) => ({
          // Si tu API no usa lazy, ignora "id" y entrega el árbol completo
          id: node && node.id !== '#' ? node.id : null,
          _ts: Date.now() // anti-caché
        }),
        // Tu endpoint devuelve { status, data }. jsTree espera un array de nodos.
        dataFilter: function (raw) {
          try {
            const res = JSON.parse(raw);
            if (res && res.status) {
              return JSON.stringify(res.data || []);
            }
            console.error(res?.message || 'Respuesta inválida del árbol');
            return '[]';
          } catch (e) {
            console.error('JSON inválido en árbol', e);
            return '[]';
          }
        },
        error: function (xhr) {
          console.error('Error de conexión', xhr);
          if (!ARBOL_INICIALIZADO) {
            $arbol.html('<div class="text-danger">Error de conexión. <span class="badge badge-info cursor-pointer" onclick="listar_tree_grupo();" ><i class="ti ti-reload"></i> Actualizar</span> </div>');
          }
        }
      }
    },
    plugins: ['search', 'contextmenu'],
    search: {
      show_only_matches: true,
      show_only_matches_children: true
    },
    contextmenu: {
      items: function (node) {
        const tree = $arbol.jstree(true);

        const esGrupo = node.parent === '#'; // Raíz = Grupo
        const esPresu = node.parent !== '#'; // Hijo = Presupuesto

        // 📁 GRUPO (Nivel 1)
        if (esGrupo) {
          const items = {
            add_group: {
              label: 'Agregar grupo',
              icon: 'ti ti-folder-plus',
              action: () => abrirModalGrupo()
            },
            add_presu: {
              label: 'Agregar presupuesto',
              icon: 'ti ti-text-plus',
              action: () => abrirModalPresupuesto(tree_id(node))
            },
            add_import_presu: {
              label: 'Importar presupuesto',
              icon: 'ti ti-text-plus',
              action: () => abrirModalImportarPresupuesto(tree_id(node))
            },
            refresh: {
              label: 'Refrescar',
              icon: 'ti ti-reload',
              action: () => tree.refresh()
            }
          };

          // Si NO es "ESCRITORIO" → habilitar Modificar y Eliminar
          if (getNodeText(node) != 'ESCRITORIO' ) {
            items.edit_group = {
              label: 'Modificar',
              icon: 'ti ti-edit',
              action: () => modificarGrupo(tree_id(node), node) // paso node para precargar
            };
            items.delete_group = {
              label: 'Eliminar',
              icon: 'ti ti-trash-x',
              action: () => eliminarNodoGrupo(tree_id(node))
            };
          }

          return items;
        }

        // 📄 PRESUPUESTO (Nivel 2)
        if (esPresu) {
          return {
            move_presu: {
              label: 'Mover de grupo',
              icon: 'ti ti-arrows-random',
              action: () => moverPresupuesto(tree_id(node))
            },
            edit_presu: {
              label: 'Modificar',
              icon: 'ti ti-edit',
              action: () => mostrarEditarPresupuesto(tree_id(node))
            },
            delete_item: {
              label: 'Eliminar',
              icon: 'ti ti-trash-x',
              action: () => eliminarNodoPresupuesto(tree_id(node))
            },
            refresh: {
              label: 'Refrescar',
              icon: 'ti ti-reload',
              action: () => tree.refresh()
            }
          };
        }

        // Fallback (por si acaso)
        return {
          refresh: {
            label: 'Refrescar',
            icon: 'ti ti-reload',
            action: () => tree.refresh()
          }
        };
      }
    }
  })
    .on('loaded.jstree', function () {
      ARBOL_INICIALIZADO = true;
    });
}

// 🔄 API pública para recarga GLOBAL desde otros lados (botón, eventos, etc.)
function recargarArbolCompleto() {
  const tree = $arbol.jstree(true);
  if (tree) tree.refresh();
  else listar_tree_grupo();
}

// Buscador
let to = false;
$('#buscador-arbol').on('keyup change', function () {
  clearTimeout(to);
  to = setTimeout(() => {
    const v = $(this).val();
    const tree = $arbol.jstree(true);
    if (tree) {
      tree.search(v);
      if (!v) tree.clear_search(); // Limpia la búsqueda si está vacío
    }
  }, 250);
});


// Select
$('#arbol-proyecto').on('select_node.jstree', function (e, data) {
  const node = data.node;
  const id = String(node.id || '');
  const tree = $('#arbol-proyecto').jstree(true);

  // Nivel 2: Presupuesto (hijo)
  if (id.startsWith('p_')) {

    // 👉 Acción para nodos hijos
    $('#div-card-presupuesto').show(); 
    $('#div-card-bienvenido').hide();
    // $('#detalle-titulo').text(node.text);
    // $('#panel-detalle').removeClass('d-none');    
    
    return;
  }

  // Nivel 1: Grupo (raíz)
  const isRoot = node.parent === '#';
  if (isRoot) {
    // 👉 Acción para nodos raíz
    $('#div-card-presupuesto').hide(); 
    $('#div-card-bienvenido').show();
    $('#div-botones-presupuesto').show(); 
    $('.alerta-bienvenido').hide(); 
    
    $('#p_idpresupuesto_grupo').val(tree_id(node));
    console.log(tree_id(node));
    
    
    
    // $('#panel-detalle').addClass('d-none'); // Oculta panel si es raíz
    // $('#detalle-titulo').text('');
    return;
  }

  // Fallback (por si acaso hay otros niveles o errores)
  console.warn('🌲 Nodo no identificado:', node);
});



// ---------- Crear Grupo ----------

function limpiar_form_grupo() {

  $('#pg_idpresupuesto_grupo').val('');
  $('#pg_descripcion').val('');
  $('#pg_icono').val('').trigger('change');
  $('#pg_icono_color').val('').trigger('change');

  // Limpiamos las validaciones
  $(".form-control").removeClass('is-valid');
  $(".form-control").removeClass('is-invalid');
  $(".error.invalid-feedback").remove();

}

function guardar_y_editar_presupuesto_grupo(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-agregar-grupo")[0]);

  var url_crear_editar_grupo = '' ;

  if ( $("#pg_idpresupuesto_grupo").val() == '' || $("#pg_idpresupuesto_grupo").val() == null ) {
    url_crear_editar_grupo = `${BASE_URL}/grupos/crear`
  } else {
    url_crear_editar_grupo = `${BASE_URL}/grupos/${$('#pg_idpresupuesto_grupo').val()}/actualizar`
    formData.append('_method', 'PUT'); // spoof para Laravel
  }

  $.ajax({
    url: url_crear_editar_grupo,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (e) {
      try {
        if (e.status == true) {

          $("#modal-agregar-grupo").modal("hide");
          listar_tree_grupo();

        } else {
          $('#resultado_pg_crear_grupo').html(`<div class="alert alert-warning">${e.msg ?? 'Ocurrió un problema'}</div>`);
        }

      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); }
      $("#guardar_registro_pg_grupo").html(`<i class="ti ti-device-floppy"></i> Importar`).removeClass('disabled send-data');
    },
    xhr: function () {
      var xhr = new window.XMLHttpRequest();
      xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
          var percentComplete = (evt.loaded / evt.total) * 100; /*console.log(percentComplete + '%');*/
          $("#barra_progress_pg_crear_grupo").css({ "width": percentComplete + '%' }).text(percentComplete.toFixed(2) + " %");
        }
      }, false);
      return xhr;
    },
    beforeSend: function () {
      $("#guardar_registro_pg_grupo").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled send-data');
      $("#barra_progress_pg_crear_grupo_div").show();
      $("#barra_progress_pg_crear_grupo").css({ width: "0%", }).text("0%");
    },
    complete: function () {
      $("#barra_progress_pg_crear_grupo_div").hide();
      $("#barra_progress_pg_crear_grupo").css({ width: "0%", }).text("0%");
    },
    error: function (jqXhr) {
      let msg = 'Error inesperado';
      if (jqXhr.responseJSON && jqXhr.responseJSON.message) { msg = jqXhr.responseJSON.message; }  // Mensaje general si viene      
      const response = jqXhr.responseJSON;                                                          // Manejo de errores en 'errors' o 'data'

      if (response) {
        let errorList = null;
        if (response.errors) { errorList = response.errors; } else if (response.data) { errorList = response.data; }
        if (errorList) {
          const list = Object.values(errorList).map(err => {
            if (Array.isArray(err)) { return err.map(e => `<li>${e}</li>`).join(''); }
            return `<li>${err}</li>`;
          }).join('');
          msg += `<ul>${list}</ul>`;
        }
      }

      $('#resultado_pg_crear_grupo').html(`<div class="alert alert-danger">${msg}</div>`);
      $("#guardar_registro_pg_grupo").html(`<i class="ti ti-device-floppy"></i> Importar`).removeClass('disabled send-data');
    }

  });
}

function abrirModalGrupo() {
  limpiar_form_grupo();
  $('#modal-agregar-grupo').modal('show');
}

function modificarGrupo(idpresupuesto_grupo, node) {  
  limpiar_form_grupo();
  $('#cargando-1-formulario').hide();
  $('#cargando-2-formulario').show();
  $('#modal-agregar-grupo').modal('show');

  $.getJSON(`${BASE_URL}/grupos/${idpresupuesto_grupo}/mostrar`, function (e, textStatus, jqXHR) {    

    $('#pg_idpresupuesto_grupo').val(e.data.idpresupuesto_grupo);
    $('#pg_descripcion').val(e.data.descripcion);
    $('#pg_icono').val(e.data.icono);
    $('#pg_icono_color').val(e.data.icono_color);

    $('#cargando-1-formulario').show();
    $('#cargando-2-formulario').hide();
  });
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       PRESUPUESTO                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
function limpiar_form_presupuesto() {

  $('#p_idpresupuesto').val('');  
  $('#p_descripcion').val('');
  $('#p_descripcion_resumen');
  $('#p_tipo').val('');
  $('#p_idproyecto').val('').trigger('change');
  $('#p_icono').val('');
  $('#p_icono_color').val('');

  // Limpiamos las validaciones
  $(".form-control").removeClass('is-valid');
  $(".form-control").removeClass('is-invalid');
  $(".error.invalid-feedback").remove();

}

function guardar_y_editar_presupuesto(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-agregar-presupuesto")[0]);

  var url_crear_editar_presupuesto = '' ;

  if ( $("#p_idpresupuesto").val() == '' || $("#p_idpresupuesto").val() == null ) {
    url_crear_editar_presupuesto = `${BASE_URL}/presupuestos/crear_cabecera`
  } else {
    url_crear_editar_presupuesto = `${BASE_URL}/presupuestos/${$('#p_idpresupuesto').val()}/actualizar_cabecera`
    formData.append('_method', 'PUT'); // spoof para Laravel
  }

  $.ajax({
    url: url_crear_editar_presupuesto,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (e) {
      try {
        if (e.status == true) {

          $("#modal-agregar-presupuesto").modal("hide");
          listar_tree_grupo();

        } else {
          $('#resultado_crear_presupuesto').html(`<div class="alert alert-warning">${e.msg ?? 'Ocurrió un problema'}</div>`);
        }

      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); }
      $("#guardar_registro_presupuesto").html(`<i class="ti ti-device-floppy"></i> Importar`).removeClass('disabled send-data');
    },
    xhr: function () {
      var xhr = new window.XMLHttpRequest();
      xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
          var percentComplete = (evt.loaded / evt.total) * 100; /*console.log(percentComplete + '%');*/
          $("#barra_progress_presupuesto").css({ "width": percentComplete + '%' }).text(percentComplete.toFixed(2) + " %");
        }
      }, false);
      return xhr;
    },
    beforeSend: function () {
      $("#guardar_registro_presupuesto").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled send-data');
      $("#barra_progress_presupuesto_div").show();
      $("#barra_progress_presupuesto").css({ width: "0%", }).text("0%");
    },
    complete: function () {
      $("#barra_progress_presupuesto_div").hide();
      $("#barra_progress_presupuesto").css({ width: "0%", }).text("0%");
    },
    error: function (jqXhr) {
      let msg = 'Error inesperado';
      if (jqXhr.responseJSON && jqXhr.responseJSON.message) { msg = jqXhr.responseJSON.message; }  // Mensaje general si viene      
      const response = jqXhr.responseJSON;                                                          // Manejo de errores en 'errors' o 'data'

      if (response) {
        let errorList = null;
        if (response.errors) { errorList = response.errors; } else if (response.data) { errorList = response.data; }
        if (errorList) {
          const list = Object.values(errorList).map(err => {
            if (Array.isArray(err)) { return err.map(e => `<li>${e}</li>`).join(''); }
            return `<li>${err}</li>`;
          }).join('');
          msg += `<ul>${list}</ul>`;
        }
      }

      $('#resultado_crear_presupuesto').html(`<div class="alert alert-danger">${msg}</div>`);
      $("#guardar_registro_presupuesto").html(`<i class="ti ti-device-floppy"></i> Importar`).removeClass('disabled send-data');
    }

  });
}

function abrirModalPresupuesto(idGrupo) {
  
  $('#p_idpresupuesto_grupo').val(idGrupo);
  $('#modal-agregar-presupuesto').modal('show');
}

function mostrarEditarPresupuesto(idpresupuesto) {
  $('#cargando-3-formulario').hide();
  $('#cargando-4-formulario').show();
  limpiar_form_presupuesto();
  $('#modal-agregar-presupuesto').modal('show');

  $.getJSON(`${BASE_URL}/presupuestos/${idpresupuesto}/mostrar`,   function (e, textStatus, jqXHR) {
    $('#p_idpresupuesto').val(e.data.idpresupuesto);
    $('#p_idpresupuesto_grupo').val(e.data.idpresupuesto_grupo);
    $('#p_descripcion').val(e.data.descripcion);
    $('#p_descripcion_resumen').val(e.data.descripcion_resumen);
    $('#p_tipo').val(e.data.tipo);
    $('#p_idproyecto').val(e.data.idproyecto).trigger('change');
    $('#p_icono').val(e.data.icono);
    $('#p_icono_color').val(e.data.icono_color);

    $('#cargando-3-formulario').show();
    $('#cargando-4-formulario').hide();
  });
}

// ---------- Eliminar (SweetAlert2 con tu patrón) ----------
async function eliminarNodoGrupo(idgrupo) {
  const url = `${BASE_URL}/grupos/${idgrupo}`;
  await eliminarConSwal(url, 'DELETE');
}

async function eliminarNodoPresupuesto(idpresupuesto) {
  const url = `${BASE_URL}/presupuestos/${idpresupuesto}`;
  await eliminarConSwal(url, 'DELETE');
}

async function eliminarConSwal(url, method) {
  Swal.fire({
    title: '¿Eliminar registro?',
    html: 'Se eliminará y no se podrá recuperar.',
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#d33",
    confirmButtonText: 'Sí, eliminar',
    preConfirm: () => {
      const form = new FormData();
      form.append('_method', method);
      form.append('_token', CSRF);

      return fetch(url, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: form
      }).then(r => { if (!r.ok) throw new Error(r.statusText || 'Error'); return r.json(); })
        .catch(e => Swal.showValidationMessage(`<b>Solicitud fallida:</b> ${e}`));
    },
    showLoaderOnConfirm: true
  }).then((result) => {
    if (!result.isConfirmed) return;
    if (result.value && result.value.status) {
      $(".tooltip").removeClass("show").addClass("hidde");
      Swal.fire('Eliminado', result.value.message || 'Registro eliminado.', 'success')
        .then(() => location.reload());
    } else {
      Swal.fire('Aviso!!', (result.value && result.value.message) || 'No se pudo eliminar.', 'info');
    }
  });
}


function syncDatosAdicionales() {
  const on = $('#p-check-datos-adicionales').prop('checked');
  if (on == true) {
    $('#div-datos-adicionales').show();
  } else {
    $('#div-datos-adicionales').hide();
  }   
}
$('#p-check-datos-adicionales').on('change', syncDatosAdicionales);

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       INICIALIZAR                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════


listar_tree_grupo();

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       J Q   F O R M   V A L I D A T I O N S                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
$(function () {

  // validamos el formulario  

  $('#pg_icono').on('change', function () { $(this).trigger('blur'); });
  $('#pg_icono_color').on('change', function () { $(this).trigger('blur'); });

  $("#form-agregar-grupo").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {
      descripcion: { required: true, minlength: 5, maxlength: 300, },
      icono: { required: true, },
      icono_color: { required: true, },
    },
    messages: {
      descripcion: { minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      icono: { required: "Campo requerido.", },
      icono_color: { required: "Campo requerido.", },
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
      guardar_y_editar_presupuesto_grupo(e);
    },
  });

  $('#pg_icono').rules('add', { required: true, messages: { required: "Campo requerido" } });
  $('#pg_icono_color').rules('add', { required: true, messages: { required: "Campo requerido" } });


  $("#form-agregar-presupuesto").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {
      descripcion:          { required: true, minlength: 5, maxlength: 300, },
      descripcion_resumen:  { required: true, minlength: 5, maxlength: 300, },
      tipo:                 { required: true,  },
      icono:                { required: true, },
      icono_color:          { required: true, },
    },
    messages: {
      descripcion:          { minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      descripcion_resumen:  { minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      tipo:                 { required: "Campo requerido.", },
      icono:                { required: "Campo requerido.", },
      icono_color:          { required: "Campo requerido.", },
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
      guardar_y_editar_presupuesto(e);
    },
  });

});