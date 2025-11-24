
const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;

$("#guardar_registro_horas").on("click", function (e)  { if ( $(this).hasClass('send-data')==false) { $("#submit-form-proyecto").submit(); } else{ toastr_warning('Procesando!!', 'Sea paciente se esta procesando.'); } });   
$("#descargar_excel_plantilla").on("click", function (e) { if ( $(this).hasClass('send-data')==false) {  $("#submit-form-descargar-excel-plantilla").submit(); } else { toastr_warning('Procesando!!', 'Sea paciente se esta procesando.'); } });   

// lista_select2("../ajax/ajax_general.php?op=select2EmpresaACargo", '#empresa_acargo', null);

$('#idempresa').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#idsocio_negocio').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   T A B L A  H O R A                                                      ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

  // === STATE CENTRAL ===
  const state = {
    page: 1,
    per_page: 10,
    q: ''      
  };

  // === UTILIDADES ===
  function humanSize(bytes) {
    if (!bytes) return '0 B';
    const units = ['B','KB','MB','GB','TB'];
    let i = 0;
    let val = Number(bytes);
    while (val >= 1024 && i < units.length - 1) { val /= 1024; i++; }
    return (Math.round(val * 10) / 10) + '' + units[i];
  }

  function setLoading(isLoading) {
    const $btn = $('#btn-buscar');
    if (isLoading) {
      $btn.prop('disabled', true).data('old-text', $btn.text()).text('Cargando...');
    } else {
      $btn.prop('disabled', false).text($btn.data('old-text') || 'Buscar');
    }
  }

  // === RENDER TABLA ===
  function renderRows(items) {
    const $tb = $('#tabla-cabeceras tbody').empty();
    if (!items || !items.length) {
      $tb.append(`<tr><td colspan="7" class="text-center text-muted">Sin resultados</td></tr>`);
      return;
    }
    items.forEach(row => {
      $tb.append(`
        <tr class="fila-importar-hora" data-idregistrohoras="${row.idregistro_horas}">
          <td class="py-1">
            <div class="btn-group btn-group-sm">
              <button class="btn btn-warning text-nowrap bnt-descargar-hora" onclick="mostrar_para_descargar(${row.idregistro_horas}, null, '.nombre_archivo_${row.idregistro_horas}')" data-toggle="tooltip" data-original-title="Descargar"><i class="ti ti-download"></i></button>
              <button class="btn btn-danger text-nowrap bn-ver-proyecto" onclick="eliminar_hora(${row.idregistro_horas})" data-toggle="tooltip" data-original-title="Eliminar"><i class="ti ti-trash-x"></i></button>
            </div>
          </td>
          <td class="py-1">${row.idregistro_horas}</td>
          <td class="py-1 nombre_archivo_${row.idregistro_horas}">${row.nombre_archivo ?? ''}</td>
          <td class="py-1">${row.sheet_name ?? ''} <small class="text-muted">#${row.sheet_index ?? ''}</small></td>
          <td class="py-1 text-end">${row.total_filas ?? 0}</td>         
          <td class="py-1 text-nowrap">${humanSize(row.file_size)}</td>
          <!-- === NUEVAS COLUMNAS: HN/HE POR DÍA === -->
          <td class="py-1 text-right"  >${row.lunes_hn ?? 0}</td>
          <td class="py-1 text-right" >${row.lunes_he ?? 0}</td>

          <td class="py-1 text-right"   >${row.martes_hn ?? 0}</td>
          <td class="py-1 text-right"  >${row.martes_he ?? 0}</td>

          <td class="py-1 text-right"  >${row.miercoles_hn ?? 0}</td>
          <td class="py-1 text-right"  >${row.miercoles_he ?? 0}</td>

          <td class="py-1 text-right"  >${row.jueves_hn ?? 0}</td>
          <td class="py-1 text-right"  >${row.jueves_he ?? 0}</td>

          <td class="py-1 text-right"  >${row.viernes_hn ?? 0}</td>
          <td class="py-1 text-right"  >${row.viernes_he ?? 0}</td>

          <td class="py-1 text-right"  >${row.sabado_hn ?? 0}</td>
          <td class="py-1 text-right" >${row.sabado_he ?? 0}</td>

          <td class="py-1 text-right" >${row.domingo_hn ?? 0}</td>
          <td class="py-1 text-right"  >${row.domingo_he ?? 0}</td>

          <!-- Totales semana (opcional, útiles) -->
          <td class="py-1 text-right fw-bold">${row.hn_semana ?? 0}</td>
          <td class="py-1 text-right fw-bold">${row.he_semana ?? 0}</td>

          <td class="py-1 text-nowrap">${row.created_at ?? ''}</td>
        </tr>
      `);
    });
  }

  // === CARGA DATOS ===
  function loadCabeceras() {
    setLoading(true);
    $.get(`${BASE_URL}/horas/cabeceras`, state)
      .done(resp => {
        if (!resp || !resp.status) {
          const msg = (resp && resp.message) ? resp.message : 'Error al cargar datos';
          alert(msg);
          return;
        }
        const data = resp.data || {};
        renderRows(data.items || []);
        const p = data.pagination || { current_page: 1, last_page: 1 };
        renderPaginacion(p.current_page, p.last_page);
      })
      .fail(xhr => {
        let msg = 'Error de red';
        if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        alert(msg);
      })
      .always(() => setLoading(false));
  }

  // === TU PAGINADOR (usa el que enviaste) ===
  function renderPaginacion(actual, total){
    const $p = $("#paginacion").empty();
    const mkItem = (label, page, disabled=false, active=false) =>
      `<li class="page-item ${disabled?'disabled':''} ${active?'active':''}">
         <a class="page-link" href="#" data-page="${page}">${label}</a>
       </li>`;

    $p.append(mkItem('Ant.', actual-1, actual<=1)); // Prev

    // Ventana centrada
    const win = 2; // muestra actual-2 ... actual+2
    let ini = Math.max(1, actual - win);
    let fin = Math.min(total, actual + win);

    if (ini > 1) {
      $p.append(mkItem('1', 1));
      if (ini > 2) $p.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
    }
    for (let i = ini; i <= fin; i++){
      $p.append(mkItem(String(i), i, false, i===actual));
    }
    if (fin < total) {
      if (fin < total-1) $p.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`);
      $p.append(mkItem(String(total), total));
    }
    $p.append(mkItem('Sig.', actual+1, actual>=total)); // Next

    // Clicks
    $p.find('a.page-link').on('click', function(e){
      e.preventDefault();
      const page = parseInt($(this).data('page'), 10);
      if (!isNaN(page) && page >= 1 && page <= total && page !== actual) {
        state.page = page;
        loadCabeceras();
      }
    });
  }

  // === EVENTOS UI ===
  $('#btn-buscar').on('click', function(){
    state.q = $('#buscar').val().trim();
    state.page = 1;
    loadCabeceras();
  });

  $('#per_page').on('change', function(){
    const v = parseInt($(this).val(), 10);
    state.per_page = isNaN(v) ? 10 : v;
    state.page = 1;
    loadCabeceras();
  });

  // Enter en el buscador
  $('#buscar').on('keypress', function(e){
    if (e.which === 13) {
      state.q = $(this).val().trim();
      state.page = 1;
      loadCabeceras();
    }
  });

  // === PRIMERA CARGA ===
  // (sin texto de búsqueda, 10 por página)
  loadCabeceras();

  $('.recargar-tabla-hora').on('click', function(){
    toastr_success('Actualizando!!', 'La tabla se esta actualizando, porfavor esperar.');
    loadCabeceras();
  });


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C R U D   I M P O R T A R   H O R A S                                             ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function limpiar_form_hora(){
  
  //Mostramos los Materiales
  $("#idproyecto").val("");
  $("#nombre_proyecto").val(`06006 CBPO-REHABILITACIÓN RED DE AGUA POTABLE, ESTACIÓN DE BOMBEO DE AGUA RESIDUALES Y COLECTORAS DEL DISTRITO DE CATACAOS, PROVINCIA DE PIURA, DEPARTAMENTO DE PIURA - SISTEMA DE AGUA POTABLE Y ALCANTARILLADO ETAPA 1 - CUI N° 2536439”`);
  $("#partida_control").val("");
  $("#concepto").val(`09012001 HRS LABORABLES`);
  $("#archivo_excel").val("");
  $("#sheet_index").html("");
  $("#preview-wrap").hide();
  $("#preview-table").html("<thead></thead><tbody></tbody>");
  $('#resultado').html('');

  $("#idempresa").val("").trigger('change');
  $("#idsocio_negocio").val("").trigger('change');

  // Limpiamos las validaciones
  $(".form-control").removeClass('is-valid');
  $(".form-control").removeClass('is-invalid');
  $(".error.invalid-feedback").remove();
}

$("#btn-excel-eliminar").on("click", function (e) { 

  $("#archivo_excel").val("");
  $("#sheet_index").html("");
  $("#preview-wrap").hide();
  $("#preview-table").html("<thead></thead><tbody></tbody>");

  toastr_warning('Removido!!!', 'El archivo seleccionado ha sido eliminado. Por favor, selecciona otro archivo antes de importar.');
});

function ver_editar_proyecto(idproyecto) {
  $("#cargando-1-formulario").hide();
  $("#cargando-2-formulario").show();
  limpiar_form_hora();
  $('#modal-importar-horas').modal('show');
  $.getJSON(`/proyectos/${idproyecto}/ver-editar`, function (e) {
    if (e.status == true) {
      $("#idproyecto").val(e.data.idproyecto);
      $("#codigo").val(e.data.codigo);
      $("#descripcion").val(e.data.descripcion);
      $("#direccion").val(e.data.direccion);
      $("#ubicacion").val(e.data.ubicacion);
      $("#fecha_inicio").val(e.data.fecha_inicio);
      $("#fecha_fin").val(e.data.fecha_fin);

      $("#cargando-1-formulario").show();
      $("#cargando-2-formulario").hide();
    } else {
      alert("No se encontró el proyecto");
    }
  }).fail(function (xhr) { ver_errores(xhr); });

}

function guardar_y_editar_hora(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-importar-horas")[0]);  

  $btnImport.prop('disabled', true);
  $spinner.removeClass('d-none').text('Importando...');

  $.ajax({
    url: `${BASE_URL}/horas/importar`,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (e) {
      try {        
        if (e.status == true) {          
          $resultado.html(`<div class="alert alert-success">${e.message}. Filas importadas: <b>${e.data.rows}</b>.</div>` );
          loadCabeceras();
          $("#modal-importar-horas").modal("hide");
          mostrar_para_descargar(e.data.idregistro_horas, e.data.nombre_archivo, null);
        }else{
          $resultado.html(`<div class="alert alert-warning">${e.msg ?? 'Ocurrió un problema'}</div>`);
        }        

      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_horas").html(`<i class="ti ti-device-floppy"></i> Importar`).removeClass('disabled send-data');
    },
    xhr: function () {
      var xhr = new window.XMLHttpRequest();
      xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
          var percentComplete = (evt.loaded / evt.total)*100; /*console.log(percentComplete + '%');*/
          $("#barra_progress_proyecto").css({"width": percentComplete+'%'}); $("#barra_progress_proyecto").text(percentComplete.toFixed(2)+" %");
        }
      }, false);
      return xhr;
    },
    beforeSend: function () {
      $("#guardar_registro_horas").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled send-data');
      $("#barra_progress_proyecto").css({ width: "0%",  });
      $("#barra_progress_proyecto").text("0%");
    },
    complete: function () {
      $("#barra_progress_proyecto").css({ width: "0%", });
      $("#barra_progress_proyecto").text("0%");
    },
    error: function (jqXhr) {
      let msg = 'Error inesperado';

      // Mensaje general si viene
      if (jqXhr.responseJSON && jqXhr.responseJSON.message) { msg = jqXhr.responseJSON.message;  }
      
      const response = jqXhr.responseJSON; // Manejo de errores en 'errors' o 'data'

      if (response) {
        let errorList = null;
        if (response.errors) {  errorList = response.errors;  } else if (response.data) {   errorList = response.data;   }
        if (errorList) {
          const list = Object.values(errorList).map(err => {              
            if (Array.isArray(err)) { return err.map(e => `<li>${e}</li>`).join(''); }
            return `<li>${err}</li>`;
          }).join('');
          msg += `<ul>${list}</ul>`;
        }
      }

      $resultado.html(`<div class="alert alert-danger">${msg}</div>`);
      $("#guardar_registro_horas").html(`<i class="ti ti-device-floppy"></i> Importar`).removeClass('disabled send-data');
    }

  });
}

function eliminar_hora(idregistro_horas ) {


  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const url  = `${BASE_URL}/importar-horas/${idregistro_horas}`;

  Swal.fire({
    title: '¿Eliminar registro?',
    html:  'Se eliminará y no se podra recuperar.',
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#d33",
    confirmButtonText:'Sí, eliminar',
    preConfirm: () => {
      const form = new FormData();
      form.append('_method', 'DELETE');
      form.append('_token', csrf);

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
      Swal.fire('Eliminado', result.value.message || 'Registro eliminado.', 'success');
    } else {
      Swal.fire('Aviso!!', (result.value && result.value.message) || 'No se pudo eliminar.', 'info');
    }
    loadCabeceras();
  });



}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C L I C K   D E R E C H O   T A B L A                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

let idregistrohoras = null;
let idpresupuesto_select = null;

// Ocultar menú al hacer clic en otro lugar
$(document).on("click", () => {  
  $("#menu-contextual-proyecto").hide(); 
  $("#menu-contextual-add-presupuesto").hide(); 
});

// Mostrar menú contextual al hacer clic derecho en fila
$(document).on("contextmenu", ".fila-importar-hora", function (e) {
  e.preventDefault();
  
  $(".fila-importar-hora").removeClass("selected");// Remover selección previa  
  $(this).addClass("selected");// Marcar esta fila como seleccionada
  idregistrohoras = $(this).data("idregistrohoras");

  $("#menu-contextual-proyecto").css({ top: e.pageY + "px", left: e.pageX + "px", }).show();
});

$("#opcion-p-eliminar").on("click", function (e) {
  e.preventDefault();
  if (idregistrohoras) {
    eliminar_hora(idregistro_horas );
  }
});

$("#opcion-p-descargar").on("click", function (e) {
  e.preventDefault();
  if (idregistrohoras) {
    mostrar_para_descargar(idregistrohoras, null,  `.nombre_archivo_${idregistrohoras}`)
  }
});


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       I M P O R T A R   H O R A S                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

const $archivo      = $('#archivo_excel');
const $sheetIndex   = $('#sheet_index');
const $btnPreview   = $('#btn-preview');
const $btnImport    = $('#btn-import');
const $spinner      = $('#spinner');
const $resultado    = $('#resultado');

function renderPreview(headers, rows){
  const $thead = $('#preview-table thead').empty();
  const $tbody = $('#preview-table tbody').empty();

  const ths = headers.map(h => `<th class="text-nowrap">${h || ''}</th>`).join('');
  $thead.append(`<tr>${ths}</tr>`);

  rows.forEach(r => {     
    const tds = r.map((v, i) => {
      let style = '';
      if (i === 1 ) { 
        style = ' class="text-nowrap"';
      } else if ( i === 22) {
        style = ' style="width:500px; min-width:500px; max-width:500px;"';
      }
      return `<td${style}>${(v === null || v === undefined) ? '' : v}</td>`;
    }).join('');

    $tbody.append(`<tr>${tds}</tr>`);
  });
  $('#preview-wrap').show();
}

function doPreview(selectedIndex=null){
  const file = $archivo[0].files[0];
  if(!file){ return; }

  const fd = new FormData();
  fd.append('_token', $('input[name="_token"]').val());
  fd.append('archivo_excel', file);
  if(selectedIndex !== null){ fd.append('sheet_index', selectedIndex); }

  $spinner.removeClass('d-none').text('Leyendo archivo...');
  $btnPreview.prop('disabled', true);
  $btnImport.prop('disabled', true);
  $sheetIndex.prop('disabled', true);
  $resultado.empty();

  $.ajax({
    url: `${BASE_URL}/horas/preview`,
    type: "POST",
    data: fd,
    contentType: false,
    processData: false,
    success: function(resp){
      if(!resp.status){ 
        $resultado.html(`<div class="alert alert-warning">No se pudo previsualizar.</div>`); 
        return;
      }
      // Llenar combo de hojas
      $sheetIndex.empty();
      resp.sheet_names.forEach((name, idx) => {
        const opt = $('<option/>',{ value: idx, text: name });
        if(idx === resp.sheet_index) opt.attr('selected', true);
        $sheetIndex.append(opt);
      });
      $sheetIndex.prop('disabled', false);

      renderPreview(resp.headers, resp.rows);
      $btnImport.prop('disabled', false);
      $btnPreview.prop('disabled', false);

      toastr_success('Documento Cargado.', 'El archivo seleccionado el archivo.');
      $("#form-importar-horas").valid();
      
    },
    error: function(xhr){
      let msg = 'Error inesperado';
      if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
      $resultado.html(`<div class="alert alert-danger">${msg}</div>`);
    },
    complete: function(){
      $spinner.addClass('d-none');
    }
  });
}

$archivo.on('change', function(){
  if(this.files && this.files.length){
    var name_archivo_new = $archivo[0].files[0].name;
    toastr_info('Procesando.', `El archivo <b>${name_archivo_new}</b> se esta procesando, profavor esperar.`);
    doPreview(null);
    
  }
});

$sheetIndex.on('change', function(){
  const idx = parseInt($(this).val(), 10);
  doPreview(idx);
});

$btnPreview.on('click', function(){
  const idx = parseInt($sheetIndex.val() || '0', 10);
  doPreview(idx);
});

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   D E S C A R G A R   H O R A                                                        ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function mostrar_para_descargar(id, nombre_archivo = null, span_nombre_archivo = null) {

  $('#span-archivo-base').html( (nombre_archivo || $(span_nombre_archivo).text()) );
  $('#idregistro_horas').val(id);
  $('#modal-descargar-archivo').modal('show');

}

function guardar_y_editar_descargar_excel(e) {  

  
  const file = $('#file_excel_plantilla')[0].files[0];
  if(!file){ alert('Selecciona una plantilla'); return; }
  if(!$('#idregistro_horas').val()){ alert('Ingresa el ID de Archivo base'); return; }

  const fd = new FormData($('#form-descargar-excel')[0]);  

  $.ajax({
    url: `${BASE_URL}/horas/plantilla/llenar`,
    method: 'POST',
    data: fd,
    cache: false,
    processData: false,
    contentType: false,
    xhrFields: { responseType: 'blob' },
    timeout: 0, // <-- sin límite de tiempo del lado cliente
    success: function(blob, status, xhr){
      let filename = 'Plantilla_Relleno.xlsx';
      const dispo = xhr.getResponseHeader('Content-Disposition') || '';
      const m = dispo.match(/filename="(.+?)"/i);
      if (m && m[1]) filename = m[1];

      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = filename;
      document.body.appendChild(a); a.click(); a.remove();
      window.URL.revokeObjectURL(url);

      $("#descargar_excel_plantilla").html('<i class="ti ti-download"></i> Descargar').removeClass('disabled send-data');
    },
    beforeSend: function () {
      $("#descargar_excel_plantilla").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled send-data');
      
    },
    complete: function () { 
      
    },
    error: function(xhr){
      let msg = 'Error al generar el archivo';
      if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
      alert(msg);
      $("#descargar_excel_plantilla").html('<i class="ti ti-download"></i> Descargar').removeClass('disabled send-data');
    },
    
  });
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   M O S T R A R   D E T A L L E   H O R A S                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

const $tablaBody = $('#tabla-detalle tbody');
const $paginacion = $('#paginacion_detalle');
const $btnBuscar = $('#btn-buscar-detalle');
const $buscar = $('#buscar_detalle');
const $filtro_color = $('#filtro_color');
const $columna = $('#columna_detalle');
const $perPage = $('#per_page_Detalle');
const $btnReload = $('.recargar-tabla-detalle');

// Estado de la tabla
const state_detalle = {
  page: 1
};

function getParams_d(pageOverride) {
  return {
    page: pageOverride || state_detalle.page || 1,
    per_page: parseInt($perPage.val(), 10) || 10,
    filtro_color: $filtro_color.val(),
    columna_detalle: $columna.val(),
    buscar: $buscar.val() || ''
  };
}

function renderRows_d(items) {
  if (!items || !items.length) {
    $tablaBody.html('<tr><td colspan="20" class="text-center text-muted">Sin resultados</td></tr>');
    return;
  }

  // Inicializar totales
  const totales = {
    lunes_hn: 0, lunes_he: 0,
    martes_hn: 0, martes_he: 0,
    miercoles_hn: 0, miercoles_he: 0,
    jueves_hn: 0, jueves_he: 0,
    viernes_hn: 0, viernes_he: 0,
    sabado_hn: 0, sabado_he: 0,
    domingo_hn: 0, domingo_he: 0,
  };

  const rows = items.map(it => {
    // Sumar totales por día
    totales.lunes_hn += Number(it.lunes_hn || 0);
    totales.lunes_he += Number(it.lunes_he || 0);
    totales.martes_hn += Number(it.martes_hn || 0);
    totales.martes_he += Number(it.martes_he || 0);
    totales.miercoles_hn += Number(it.miercoles_hn || 0);
    totales.miercoles_he += Number(it.miercoles_he || 0);
    totales.jueves_hn += Number(it.jueves_hn || 0);
    totales.jueves_he += Number(it.jueves_he || 0);
    totales.viernes_hn += Number(it.viernes_hn || 0);
    totales.viernes_he += Number(it.viernes_he || 0);
    totales.sabado_hn += Number(it.sabado_hn || 0);
    totales.sabado_he += Number(it.sabado_he || 0);
    totales.domingo_hn += Number(it.domingo_hn || 0);
    totales.domingo_he += Number(it.domingo_he || 0);

    return `
      <tr>
        <td class="py-0 text-center">
          <button class="py-0 btn btn-sm btn-warning text-nowrap bnt-descargar-hora" onclick="mostrar_para_descargar(${it.idregistro_horas}, null, '.nombre_archivo_d_${it.idregistro_horas_detalle}_${it.idregistro_horas}')" data-toggle="tooltip" data-original-title="Descargar"><i class="ti ti-download"></i></button>
        </td>
        <td class="py-0">${it.nro_hoja}</td>
        <td class="py-0 ">${it.apellidos_nombres}</td>
        <td class="py-0 celda-editable" data-id="${it.idregistro_horas_detalle}" data-field="dni">${it.dni}</td>

        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="lunes_hn" data-toggle="tooltip" title="${it.lunes_hn_nombre_color}" ${(it.lunes_hn_bg_color == null || it.lunes_hn_bg_color == '' ? '' : `style="background-color: ${it.lunes_hn_bg_color};"`)} >${it.lunes_hn ?? 0}</td>
        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="lunes_he" data-toggle="tooltip" title="${it.lunes_he_nombre_color}" ${(it.lunes_he_bg_color == null || it.lunes_he_bg_color == '' ? '' : `style="background-color: ${it.lunes_he_bg_color};"`)} >${it.lunes_he ?? 0}</td>

        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="martes_hn" data-toggle="tooltip" title="${it.martes_hn_nombre_color}" ${(it.martes_hn_bg_color == null || it.martes_hn_bg_color == '' ? '' : `style="background-color: ${it.martes_hn_bg_color};"`)}  >${it.martes_hn ?? 0}</td>
        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="martes_he" data-toggle="tooltip" title="${it.martes_he_nombre_color}" ${(it.martes_he_bg_color == null || it.martes_he_bg_color == '' ? '' : `style="background-color: ${it.martes_he_bg_color};"`)} >${it.martes_he ?? 0}</td>

        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="miercoles_hn" data-toggle="tooltip" title="${it.miercoles_hn_nombre_color}" ${(it.miercoles_hn_bg_color == null || it.miercoles_hn_bg_color == '' ? '' : `style="background-color: ${it.miercoles_hn_bg_color};"`)} >${it.miercoles_hn ?? 0}</td>
        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="miercoles_he" data-toggle="tooltip" title="${it.miercoles_he_nombre_color}" ${(it.miercoles_he_bg_color == null || it.miercoles_he_bg_color == '' ? '' : `style="background-color: ${it.miercoles_he_bg_color};"`)} >${it.miercoles_he ?? 0}</td>

        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="jueves_hn" data-toggle="tooltip" title="${it.jueves_hn_nombre_color}" ${(it.jueves_hn_bg_color == null || it.jueves_hn_bg_color == '' ? '' : `style="background-color: ${it.jueves_hn_bg_color};"`)} >${it.jueves_hn ?? 0}</td>
        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="jueves_he" data-toggle="tooltip" title="${it.jueves_he_nombre_color}" ${(it.jueves_he_bg_color == null || it.jueves_he_bg_color == '' ? '' : `style="background-color: ${it.jueves_he_bg_color};"`)} >${it.jueves_he ?? 0}</td>

        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="viernes_hn" data-toggle="tooltip" title="${it.viernes_hn_nombre_color}" ${(it.viernes_hn_bg_color == null || it.viernes_hn_bg_color == '' ? '' : `style="background-color: ${it.viernes_hn_bg_color};"`)} >${it.viernes_hn ?? 0}</td>
        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="viernes_he" data-toggle="tooltip" title="${it.viernes_he_nombre_color}" ${(it.viernes_he_bg_color == null || it.viernes_he_bg_color == '' ? '' : `style="background-color: ${it.viernes_he_bg_color};"`)} >${it.viernes_he ?? 0}</td>

        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="sabado_hn" data-toggle="tooltip" title="${it.sabado_hn_nombre_color}" ${(it.sabado_hn_bg_color == null || it.sabado_hn_bg_color == '' ? '' : `style="background-color: ${it.sabado_hn_bg_color};"`)} >${it.sabado_hn ?? 0}</td>
        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="sabado_he" data-toggle="tooltip" title="${it.sabado_he_nombre_color}" ${(it.sabado_he_bg_color == null || it.sabado_he_bg_color == '' ? '' : `style="background-color: ${it.sabado_he_bg_color};"`)} >${it.sabado_he ?? 0}</td>

        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="domingo_hn" data-toggle="tooltip" title="${it.domingo_hn_nombre_color}" ${(it.domingo_hn_bg_color == null || it.domingo_hn_bg_color == '' ? '' : `style="background-color: ${it.domingo_hn_bg_color};"`)} >${it.domingo_hn ?? 0}</td>
        <td class="py-0 text-right celda-editable" data-id="${it.idregistro_horas_detalle}"data-field="domingo_he" data-toggle="tooltip" title="${it.domingo_he_nombre_color}" ${(it.domingo_he_bg_color == null || it.domingo_he_bg_color == '' ? '' : `style="background-color: ${it.domingo_he_bg_color};"`)} >${it.domingo_he ?? 0}</td>        

        <td class="py-0" ><div class="bg-light" style="overflow: auto; resize: both; height: 45px; width: 300px;"> ${it.observaciones} </div></td>
        <td class="py-0 text-nowrap nombre_archivo_d_${it.idregistro_horas_detalle}_${it.idregistro_horas}">${it.nombre_archivo}</td>
      </tr>
    `;
  }).join('');

  // Fila de totales
  const totalRow = `
    <tr class="table-success font-weight-bold">
      <td colspan="4" class="py-1 text-right">Total</td>

      <td class="py-1">${totales.lunes_hn}</td>
      <td class="py-1">${totales.lunes_he}</td>

      <td class="py-1">${totales.martes_hn}</td>
      <td class="py-1">${totales.martes_he}</td>

      <td class="py-1">${totales.miercoles_hn}</td>
      <td class="py-1">${totales.miercoles_he}</td>

      <td class="py-1">${totales.jueves_hn}</td>
      <td class="py-1">${totales.jueves_he}</td>

      <td class="py-1">${totales.viernes_hn}</td>
      <td class="py-1">${totales.viernes_he}</td>

      <td class="py-1">${totales.sabado_hn}</td>
      <td class="py-1">${totales.sabado_he}</td>

      <td class="py-1">${totales.domingo_hn}</td>
      <td class="py-1">${totales.domingo_he}</td>

      <td class="py-1 text-center">—</td>
    </tr>
  `;

  $tablaBody.html(rows + totalRow);
}


function renderPagination_d(meta) {
  // meta: current_page, last_page, total, per_page
  if (!meta || meta.last_page <= 1) {
    $paginacion.empty();
    return;
  }

  const curr = meta.current_page;
  const last = meta.last_page;

  // Ventana alrededor de la página actual
  let start = Math.max(1, curr - 2);
  let end = Math.min(last, curr + 2);

  // Asegurar 5 botones si es posible
  if (end - start < 4) {
    const deficit = 4 - (end - start);
    start = Math.max(1, start - deficit);
    end = Math.min(last, end + deficit);
  }

  let html = '';

  // Prev
  html += `
      <li class="page-item ${curr === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${curr - 1}">&laquo;</a>
      </li>
    `;

  // Primeros y elipsis
  if (start > 1) {
    html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
    if (start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
  }

  // Números
  for (let p = start; p <= end; p++) {
    html += `
        <li class="page-item ${p === curr ? 'active' : ''}">
          <a class="page-link" href="#" data-page="${p}">${p}</a>
        </li>
      `;
  }

  // Últimos y elipsis
  if (end < last) {
    if (end < last - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
    html += `<li class="page-item"><a class="page-link" href="#" data-page="${last}">${last}</a></li>`;
  }

  // Next
  html += `
      <li class="page-item ${curr === last ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${curr + 1}">&raquo;</a>
      </li>
    `;

  $paginacion.html(html);
}

function cargarTabla(pageOverride) {
  const params = getParams_d(pageOverride);
  // Mantener estado de página actual
  state_detalle.page = params.page;

  // (Opcional) loading
  $tablaBody.html('<tr><td colspan="19" class="text-center text-muted">Cargando…</td></tr>');

  $.getJSON(`${BASE_URL}/horas/mostrar_detalle`, params, function (e) {
    if (!e || e.status !== true) {
      console.error(e && e.message ? e.message : 'Error desconocido');
      $tablaBody.html('<tr><td colspan="19" class="text-center text-danger">Error al cargar</td></tr>');
      $paginacion.empty();
      return;
    }

    const items = e.data.items || [];
    const meta = e.data.meta || null;

    renderRows_d(items);
    renderPagination_d(meta);
    // $('[data-toggle="tooltip"]').tooltip();
  }).fail(function (xhr) {
    console.error(xhr);
    $tablaBody.html('<tr><td colspan="19" class="text-center text-danger">No se pudo obtener datos</td></tr>');
    $paginacion.empty();
  });
}

// Eventos
$btnBuscar.on('click', function () {
  state_detalle.page = 1;
  cargarTabla(1);
});

// Enter en el input buscar
$buscar.on('keyup', function (e) {
  if (e.key === 'Enter') {
    state_detalle.page = 1;
    cargarTabla(1);
  }
});

// Cambio de columna
// $columna.on('change', function() {
//   state_detalle.page = 1;
//   cargarTabla(1);
// });

// Cambiar registros por página
$perPage.on('change', function () {
  state_detalle.page = 1;
  cargarTabla(1);
});

// Botón recargar
$btnReload.on('click', function () {
  // Mantiene criterios de búsqueda y per_page actuales
  cargarTabla(state_detalle.page || 1);
});

// Click en paginación (delegado)
$paginacion.on('click', 'a[data-page]', function (e) {
  e.preventDefault();
  const p = parseInt($(this).data('page'), 10);
  if (!isNaN(p)) {
    cargarTabla(p);
  }
});

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       ACTUALIZAR DETALLE DE CELDA                                                               ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// Doble click: activar input
$(document).on('dblclick', 'td.celda-editable', function () {
  const $td = $(this);
  if ($td.find('input.celda-input').length) return; // evitar 2 inputs

  const original = String($.trim($td.text() || '0'));
  const $input = $('<input>', {
    type: 'number',
    class: 'form-control form-control-sm celda-input text-right',
    min: 0, max: 24, step: '0.25', // ajusta a tu rango/paso
    value: original
  }).css({ width: '80px', padding: '0 .25rem' });

  $td.data('original', original).empty().append($input);
  $input.focus().select();

  // Guardar con Enter
  $input.on('keydown', (ev) => {
    if (ev.key === 'Enter') {
      ev.preventDefault();
      guardar_cambio_celda_detalle($td, $input.val());
    } else if (ev.key === 'Escape') {
      ev.preventDefault();
      cancelar_editar_celda_detalle($td);
    }
  });

  // Blur cancela (no guarda)
  $input.on('blur', () => cancelar_editar_celda_detalle($td));
});

function cancelar_editar_celda_detalle($td) {
  const original = $td.data('original');
  $td.removeData('original').text(original);
}

function guardar_cambio_celda_detalle($td, value) {
  const idDetalle = $td.data('id');
  const field     = $td.data('field');
  var valor_final = '';

  // Define qué campos son de texto y cuáles son horas
  const camposTexto = new Set([
    'dni','apellidos_nombres','cargo','observaciones',
    'proyecto','partida_control','concepto',
    'nro_hoja','fecha_ingreso','hijos'
  ]);
  const camposHora = new Set([
    'lunes_hn','lunes_he','martes_hn','martes_he','miercoles_hn','miercoles_he',
    'jueves_hn','jueves_he','viernes_hn','viernes_he','sabado_hn','sabado_he','domingo_hn','domingo_he',
  ]);

  if (camposTexto.has(field)) {
    valor_final = value;
  } else if (camposHora.has(field)) {
    // === CAMPOS DE HORAS: aplicar validación numérica 0–24 con 2 decimales ===
    let nuevo = (value === '' || value == null) ? 0 : parseFloat(value);
    if (isNaN(nuevo)) nuevo = 0;
    if (nuevo > 24) { toastr_info('Máximo 24h', 'El máximo de horas permitidas es 24'); return; }
    if (nuevo < 0)  { toastr_info('Valor inválido', 'No puede ser negativo'); return; }
    valor_final = Math.round(nuevo * 100) / 100;
  } else {
    // Campo no permitido
    toastr_info('Campo no permitido', `No puedes editar: ${field}`);
    return;
  }  

  const url = `${BASE_URL}/registro-horas-detalle/${idDetalle}/celda`;

  // bloquear input mientras envía
  // $td.find('input.celda-input').prop('disabled', true);
  $td.html('<i class="ti ti-rotate-clockwise-2 fa fa-spin"></i>');

  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: url,
    method: 'PATCH',
    dataType: 'json',
    data: { field, value: valor_final }
  })
  .done((e) => {
    if (e && e.status == true) {
      // refresca toda la tabla con tu función
      $td.html(e.data.value);
    } else {
      let msg = (e && e.message) ? e.message : 'No se pudo guardar.';
      if (e && e.data) {
        const first = Object.values(e.data)[0];
        if (first && first.length) msg = first[0];
      }
      $td.html('error');
      cancelar_editar_celda_detalle($td);
    }
  }).fail((xhr) => {
    let msg = 'Error al guardar.';
    const r = xhr.responseJSON;
    if (r && r.message) msg = r.message;
    $td.html('error');
    cancelar_editar_celda_detalle($td);
  });
}


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       J Q   F O R M   V A L I D A T I O N S                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
$(function () {    

  // validamos el formulario  

  $('#fecha_pago_obrero').on('change', function() { $(this).trigger('blur'); });
  $('#fecha_valorizacion').on('change', function() { $(this).trigger('blur'); });
  $('#empresa_acargo').on('change', function() { $(this).trigger('blur'); });

  $("#form-importar-horas").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {
      nombre_proyecto:  { minlength: 5, maxlength: 300, },
      partida_control:  { minlength: 4, maxlength: 300 },
      concepto:         { minlength: 4, maxlength: 300 },
      sheet_index:      { required: true, },      
      archivo_excel:    { required: true,  extension: "xls|xlsx|xlsm"  }
    },
    messages: {
      nombre_proyecto:  { minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      partida_control:  { minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      concepto:         { minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      sheet_index:      { required: "Campo requerido.", },      
      archivo_excel:    { required: "Campo requerido.",   extension: "Solo archivos Excel (.xls, .xlsx)."  }
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
      guardar_y_editar_hora(e);       
    },
  });

  $('#fecha_pago_obrero').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#fecha_valorizacion').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#empresa_acargo').rules('add', { required: true, messages: {  required: "Campo requerido" } });


  $("#form-descargar-excel").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {
      
      file_excel_plantilla:    { required: true,    }
    },
    messages: {      
      file_excel_plantilla:    { required: "Campo requerido.",    }
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
      guardar_y_editar_descargar_excel(e);       
    },
  });

});