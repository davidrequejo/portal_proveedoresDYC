const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

$("#guardar_registro_banco").on("click", function (e) { $("#submit-form-banco").submit(); });   


function show_hide_escenario(flag) {
  if (flag == 1) {            // Tabla principal
    $('#div-tabla-principal-banco').show();
    $(".btn-agregar-banco").show();
    $(".btn-cancelar").hide();
    
  } else if (flag == 2) {     // Detalle proyecto
    $('#div-tabla-principal-banco').hide();
    $(".btn-agregar-banco").hide();
    $(".btn-cancelar").show();
  } else if (flag == 3) {     //
  } else if (flag == 4) {
    
  }
}


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   T A B L A   P R O Y E C T O                                                        ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

const state = {
  page: 1,
  per_page: 10,
  sort: 'codigo',
  dir: 'asc',
  q: ''
};

// Cargar datos
function tabla_principal_cargar_banco(){
  
  $.getJSON(`${BASE_URL}/banco/tabla_principal`, state, function(res){

    console.log(res.data);
    
    renderFilas(res.data);
    renderPaginacion(res.current_page, res.last_page);
    marcarOrden(state.sort, state.dir);
  }).fail(function (xhr) { ver_errores(xhr); });
}

// Render filas de la tabla
function renderFilas(rows){
  const $tb = $("#tabla-banco tbody").empty();

  if (!rows || rows.length === 0){
    $tb.append('<tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>');
    return;
  }

  rows.forEach(r => {
    let estado = r.estado_trash == '1'
      ? '<span class="badge badge-success">Activo</span>'
      : '<span class="badge badge-danger">Inactivo</span>';

    $tb.append(`
      <tr class="fila-banco" data-id="${r.idbanco}">
        <td class="py-1 text-center">
          <div class="btn-group btn-group-sm">
            <button class="btn" onclick="ver_editar_banco(${r.idbanco})">
              <i class="fas fa-pencil-alt color_icon_opt"></i>
            </button>
            <button class="btn " onclick="eliminar_banco(${r.idbanco}, '${r.descripcion}')">
              <i class="fas fa-trash color_icon_opt"></i>
            </button>
          </div>
        </td>
        <td class="py-1 text-center">${String(r.idbanco).padStart(3,'0')}</td>
        <td class="py-1 text-center">${r.codigo_bank_s10}</td>
        <td class="py-1">${r.descripcion}</td>
        <td class="py-1 ">${r.abreviatura ?? ''}</td>
        <td class="py-1 text-center">${estado}</td>
      </tr>
    `);
  });
}

// Render paginación Bootstrap (ventana de 5 páginas)
function renderPaginacion(actual, total){
  const $p = $("#paginacion").empty();
  const mkItem = (label, page, disabled=false, active=false) => `<li class="page-item ${disabled?'disabled':''} ${active?'active':''}"> <a class="page-link" href="#" data-page="${page}">${label}</a> </li>`;

  $p.append(mkItem('Ant.', actual-1, actual<=1)); // Prev

  // Ventana centrada
  const win = 2; // muestra actual-2 ... actual+2
  let ini = Math.max(1, actual - win);
  let fin = Math.min(total, actual + win);

  if (ini > 1) { $p.append(mkItem('1', 1)); if (ini > 2) $p.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`); }
  for (let i = ini; i <= fin; i++){  $p.append(mkItem(String(i), i, false, i===actual)); }
  if (fin < total) { if (fin < total-1) $p.append(`<li class="page-item disabled"><span class="page-link">…</span></li>`); $p.append(mkItem(String(total), total));  }    
  $p.append(mkItem('Sig.', actual+1, actual>=total));// Next
}

// Marcar orden visualmente
function marcarOrden(col, dir){
  $("#tabla-bancos thead th.sortable").each(function(){ const $th = $(this);  const c = $th.data('sort');  $th.removeClass('asc desc'); if (c === col) $th.addClass(dir);  });
}

// Eventos: click en paginación
$("#paginacion").on("click", "a.page-link", function(e){  
  $("#tabla-bancos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state.page = Math.max(1, page); tabla_principal_cargar_banco(); } 
});

// Eventos: ordenar al hacer clic en header
$("#tabla-bancos thead").on("click", "th.sortable", function(){
  $("#tabla-bancos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Ordenando...</td></tr>');
  const col = $(this).data("sort"); if (state.sort === col) { state.dir = (state.dir === 'asc') ? 'desc' : 'asc'; } else { state.sort = col;  state.dir  = 'asc'; } state.page = 1;    
  tabla_principal_cargar_banco();
});

// Búsqueda con debounce
let t = null;
$("#buscar").on("input", function(){
  $("#tabla-bancos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  const val = $(this).val(); clearTimeout(t); t = setTimeout(function(){ state.q = val; state.page = 1; tabla_principal_cargar_banco(); }, 300);
});

// Cambiar tamaño de página
$("#perPage").on("change", function(){
  $("#tabla-bancos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
  state.per_page = parseInt($(this).val(), 10) || 20;  state.page = 1;
  tabla_principal_cargar_banco();
});

// Carga inicial
tabla_principal_cargar_banco();

$(".recargar-tabla-banco").on("click", function(){
  toastr_info('<i class="ti ti-checks"></i> Actualizando...', 'Los datos se estan actualizado', 500);
  $("#tabla-bancos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');    

  tabla_principal_cargar_banco();
});

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════


function limpiar_form_banco(){
  $("#idbanco").val('');
  $("#codigo_bank_s10").val('');
  $("#descripcion").val('');
  $("#abreviatura").val('');

  $(".form-control").removeClass('is-valid is-invalid');
  $(".error.invalid-feedback").remove();
}

function ver_editar_banco(idbanco){

  limpiar_form_banco();
  $('#modal-agregar-banco').modal('show');

  $.getJSON(`${BASE_URL}/banco/${idbanco}/ver-editar`, function (e) {

    if (e.status === true) {
      $("#idbanco").val(e.data.idbanco);
      $("#codigo_bank_s10").val(e.data.codigo_bank_s10);
      $("#descripcion").val(e.data.descripcion);
      $("#abreviatura").val(e.data.abreviatura);
    } else {
      toastr.error('Banco no encontrado');
    }

  }).fail(function (xhr) {
    ver_errores(xhr);
  });
}

function guardar_y_editar_banco(e){

  let formData = new FormData($("#form-agregar-banco")[0]);
  let id = $("#idbanco").val();
  let url = '';

  if (id === '') {
    url = `${BASE_URL}/banco/crear_banco`;
  } else {
    url = `${BASE_URL}/banco/editar_banco/${id}`;
    formData.append('_method', 'PUT');
  }

  $.ajax({
    url: url,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (e) {
      if (e.status === true) {
        tabla_principal_cargar_banco();
        limpiar_form_banco();
        Swal.fire("Correcto!", "Banco guardado correctamente", "success");
        $("#modal-agregar-banco").modal("hide");
      } else {
        ver_errores(e);
      }
    },
    error: function (xhr) {
      ver_errores(xhr);
    }
  });
}

function eliminar_banco(idbanco, descripcion){

  Swal.fire({
    title: "¿Eliminar banco?",
    html: `<b class="text-danger">${descripcion}</b>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar"
  }).then((result) => {

    if (result.isConfirmed) {

      $.ajax({
        url: `${BASE_URL}/banco/eliminar_banco/${idbanco}`,
        type: "PUT",
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (e) {
          if (e.status === true) {
            Swal.fire("Eliminado!", "Banco eliminado correctamente", "success");
            tabla_principal_cargar_banco();
          } else {
            Swal.fire("Error", e.message, "error");
          }
        }
      });
    }
  });
}


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C L I C K   D E R E C H O   T A B L A                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

let idproyecto_select = null;
let idpresupuesto_select = null;

// Ocultar menú al hacer clic en otro lugar
$(document).on("click", () => {  
  $("#menu-contextual-proyecto").hide(); 
  $("#menu-contextual-add-presupuesto").hide(); 

});

// Mostrar menú contextual al hacer clic derecho en fila
$(document).on("contextmenu", ".fila-proyecto", function (e) {
  e.preventDefault();
  
  $(".fila-proyecto").removeClass("selected");// Remover selección previa  
  $(this).addClass("selected");// Marcar esta fila como seleccionada
  idproyecto_select = $(this).data("id");

  $("#menu-contextual-proyecto").css({ top: e.pageY + "px", left: e.pageX + "px", }).show();
});

// Opciones del menú contextual
$("#opcion-p-editar").on("click", function (e) {
  e.preventDefault();
  if (idproyecto_select) {
    ver_editar_tipoestandar(idproyecto_select);
  }
});

$("#opcion-p-ver-detalle").on("click", function (e) {
  e.preventDefault();
  if (idproyecto_select) {
    ver_detalle_proyecto(idproyecto_select);
  }
});

$("#opcion-p-eliminar").on("click", function (e) {
  e.preventDefault();
  if (idproyecto_select) {
    toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
  }
});

$("#opcion-p-enviar-terminado").on("click", function (e) {
  e.preventDefault();
  if (idproyecto_select) {
    toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
  }
});

$("#opcion-p-enviar-papelera").on("click", function (e) {
  e.preventDefault();
  if (idproyecto_select) {
    toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
  }
});

$(document).on("contextmenu", ".fila-proyecto-presupuesto", function (e) {
  e.preventDefault();
  
  $(".fila-proyecto-presupuesto").removeClass("selected");// Remover selección previa  
  $(this).addClass("selected");// Marcar esta fila como seleccionada
  idpresupuesto_select = $(this).data("idpresupuesto");

  if (idpresupuesto_select == null || idpresupuesto_select == '') {
    $('#opcion-ap-agregar').show();
    $('#opcion-ap-ver-detalle').hide();
    $('#opcion-ap-actualizar').hide();
    $('#opcion-ap-eliminar').hide();
  } else {
    $('#opcion-ap-agregar').hide();
    $('#opcion-ap-ver-detalle').show();
    $('#opcion-ap-actualizar').show();
    $('#opcion-ap-eliminar').show();
  }

  $("#menu-contextual-add-presupuesto").css({ top: e.pageY + "px", left: e.pageX + "px", }).show();
});

$("#opcion-ap-agregar").on("click", function (e) {
  e.preventDefault();
  $('#modal-agregar-persona').modal('show');
});

$("#opcion-ap-ver-detalle").on("click", function (e) {
  e.preventDefault();
  toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
});

$("#opcion-ap-actualizar").on("click", function (e) {
  e.preventDefault();
  toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
});

$("#opcion-ap-eliminar").on("click", function (e) {
  e.preventDefault();
  toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
});

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       J Q   F O R M   V A L I D A T I O N S                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
$(function () {    

  $("#form-agregar-banco").validate({
    rules: {
      codigo_bank_s10: { required: true },
      descripcion:     { required: true }
    },
    messages: {
      codigo_bank_s10: { required: "Campo requerido" },
      descripcion:     { required: "Campo requerido" }
    },
    submitHandler: function (e) {
      guardar_y_editar_banco(e);
    }
  });
});

