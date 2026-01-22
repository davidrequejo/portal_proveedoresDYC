const BASE_URLl = document.querySelector('meta[name="app-url"]').content;
const CSRFF = document.querySelector('meta[name="csrf-token"]').content;

$("#guardar_registro_cuenta_bank").on("click", function (e) { $("#submit-cuentabancaria").submit(); });   


lista_select2(`${BASE_URLl}/select2/bancos`, '#idbanco');

$("#idbanco").select2({ theme: "bootstrap4", placeholder: "Selec. Banco", allowClear: true, });

$("#tipocuenta").select2({ theme: "bootstrap4", placeholder: "Tipo Cuenta", allowClear: true, });

$("#moneda").select2({ theme: "bootstrap4", placeholder: "Selec. moneda", allowClear: true, });
$("#predeterminado").select2({ theme: "bootstrap4", placeholder: "Selec. si es predeterminado", allowClear: true, });

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
function tabla_principal_cnta_bank(){
  
  $.getJSON(`${BASE_URLl}/persona-cuenta-bancaria/tabla_principal`, state, function(res){

    console.log(res.data);
    
    renderFilas(res.data);
    renderPaginacion(res.current_page, res.last_page);
    marcarOrden(state.sort, state.dir);
  }).fail(function (xhr) { ver_errores(xhr); });
}

// Render filas de la tabla
function renderFilas(rows){
  const $tb = $("#tbl_lista_cuentas_bancarias tbody").empty();

  if (!rows || rows.length === 0){
    $tb.append('<tr><td colspan="9" class="text-center text-muted">Sin resultados</td></tr>');
    return;
  }

  rows.forEach(r => {
    let estado = r.estado_trash == '1' ? '<span class="badge badge-new">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';

    let tipoCuenta = '';
    const tipocuenta = (r.estado_revision === null);

    switch (r.tipocuenta) {
        case 'C': tipoCuenta = `Corriente`; break;
        case 'A': tipoCuenta = `Ahorros`; break;
        case 'M': tipoCuenta = `Maestra`; break;
        case 'T': tipoCuenta = `CTS`; break;
        case 'D': tipoCuenta = `Detracción`; break;
        case 'S': tipoCuenta = `Cuenta Sueldo`; break;
    }

    $tb.append(`
      <tr class="fila-banco" data-id="${r.idpersona_CuentaBancaria}">
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn" onclick="ver_editar_cuentabancaria(${r.idpersona_CuentaBancaria})">
              <i class="fas fa-pencil-alt color_icon_opt"></i>
            </button>
            <button class="btn" onclick="eliminar_cuentabancaria(${r.idpersona_CuentaBancaria}, '${r.banco}')">
              <i class="fas fa-trash color_icon_opt"></i>
            </button>
          </div>
        </td>
        <td>${r.banco}</td>
        <td>${tipoCuenta}</td>
        <td>${r.numero_cuenta ?? ''}</td>
        <td>${r.predeterminado}</td>
        <td>${r.moneda}</td>
        <td>${r.cuenta_interbancaria}</td>
        <td>${r.numero_cuenta_abono}</td>
        <td>${estado}</td>
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
  $("#tbl_lista_cuentas_bancarias thead th.sortable").each(function(){ const $th = $(this);  const c = $th.data('sort');  $th.removeClass('asc desc'); if (c === col) $th.addClass(dir);  });
}

// Eventos: click en paginación
$("#paginacion").on("click", "a.page-link", function(e){  
  $("#tbl_lista_cuentas_bancarias tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state.page = Math.max(1, page); tabla_principal_cnta_bank(); } 
});

// Eventos: ordenar al hacer clic en header
$("#tbl_lista_cuentas_bancarias thead").on("click", "th.sortable", function(){
  $("#tbl_lista_cuentas_bancarias tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Ordenando...</td></tr>');
  const col = $(this).data("sort"); if (state.sort === col) { state.dir = (state.dir === 'asc') ? 'desc' : 'asc'; } else { state.sort = col;  state.dir  = 'asc'; } state.page = 1;    
  tabla_principal_cnta_bank();
});

// Búsqueda con debounce
let t = null;
$("#buscar").on("input", function(){
  $("#tbl_lista_cuentas_bancarias tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  const val = $(this).val(); clearTimeout(t); t = setTimeout(function(){ state.q = val; state.page = 1; tabla_principal_cnta_bank(); }, 300);
});

// Cambiar tamaño de página
$("#perPage").on("change", function(){
  $("#tbl_lista_cuentas_bancarias tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
  state.per_page = parseInt($(this).val(), 10) || 20;  state.page = 1;
  tabla_principal_cnta_bank();
});

// Carga inicial
tabla_principal_cnta_bank();

$(".recargar-tabla-proyecto").on("click", function(){
  toastr_info('<i class="ti ti-checks"></i> Actualizando...', 'Los datos se estan actualizado', 500);
  $("#tbl_lista_cuentas_bancarias tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');    

  tabla_principal_cnta_bank();
});

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════


function limpiar_form_banco(){
  $("#idpersona_CuentaBancaria").val('');
  $("#idbanco").val('');
  $("#tipocuenta").val('');
  $("#moneda").val('');
  $("#predeterminado").val('');
  $("#numero_cuenta").val('');
  $("#cuenta_interbancaria").val('');

  $(".form-control").removeClass('is-valid is-invalid');
  $(".error.invalid-feedback").remove();
}

function ver_editar_cuentabancaria(idpersona_CuentaBancaria){

  limpiar_form_banco();
  $('#modal-crear_cuentabancaria').modal('show');

  $.getJSON(`${BASE_URLl}/persona-cuenta-bancaria/${idpersona_CuentaBancaria}/ver-editar`, function (e) {

    if (e.status === true) {
      $("#idpersona_CuentaBancaria").val(e.data.idpersona_CuentaBancaria);
      $("#idpersona").val(e.data.idpersona);
      $("#idbanco").val(e.data.idbanco).trigger('change');
      $("#tipocuenta").val(e.data.tipocuenta).trigger('change');
      $("#moneda").val(e.data.moneda).trigger('change');
      $("#predeterminado").val(e.data.predeterminado).trigger('change');
      $("#numero_cuenta").val(e.data.numero_cuenta);
      $("#cuenta_interbancaria").val(e.data.cuenta_interbancaria);
    } else {
      toastr.error('Registro no encontrado');
    }

  }).fail(function (xhr) {
    ver_errores(xhr);
  });
}

function guardar_y_editar_cuentabancaria(e){

  let formData = new FormData($("#form-cuenta-bancaria")[0]);
  let id = $("#idpersona_CuentaBancaria").val();
  let url = '';

  if (id === '') {
    url = `${BASE_URLl}/persona-cuenta-bancaria/crear`;
  } else {
    url = `${BASE_URLl}/persona-cuenta-bancaria/editar/${id}`;
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
        tabla_principal_cnta_bank();
        limpiar_form_banco();
        Swal.fire("Correcto!", "Banco guardado correctamente", "success");
        $("#modal-crear_cuentabancaria").modal("hide");
      } else {
        ver_errores(e);
      }
    },
    error: function (xhr) {
      ver_errores(xhr);
    }
  });
}

function eliminar_cuentabancaria(id, descripcion){

  Swal.fire({
    title: "¿Eliminar banco?",
    html: `<b class="text-danger">${descripcion}</b>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar"
  }).then((result) => {

    if (result.isConfirmed) {

      $.ajax({
        url: `${BASE_URLl}/persona-cuenta-bancaria/eliminar/${id}`,
        type: "PUT",
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (e) {
          if (e.status === true) {
            Swal.fire("Eliminado!", "Banco eliminado correctamente", "success");
            tabla_principal_cnta_bank();
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

  $('#idbanco').on('change', function() { $(this).trigger('blur'); });
  $('#tipocuenta').on('change', function() { $(this).trigger('blur'); });
  $('#moneda').on('change', function() { $(this).trigger('blur'); });
  $('#predeterminado').on('change', function() { $(this).trigger('blur'); });

  $("#form-cuenta-bancaria").validate({
    rules: {
      idbanco: { required: true },
      tipocuenta: { required: true },
      moneda:     { required: true },
      predeterminado:     { required: true },
      numero_cuenta: { required: true, number: true },
      cuenta_interbancaria: { required: true, number: true },
    },
    messages: {
      idbanco: { required: "Campo requerido" },
      tipocuenta: { required: "Campo requerido" },
      moneda:     { required: "Campo requerido" },
      predeterminado:     { required: "Campo requerido" },
      numero_cuenta: { required: "Campo requerido", number: "Ingrese un valor numérico" },
      cuenta_interbancaria: { required: "Campo requerido", number: "Ingrese un valor numérico" },

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
      guardar_y_editar_cuentabancaria(e);       
    },
  });

  $('#idbanco').on('change', function() { $(this).trigger('blur'); });
  $('#tipocuenta').on('change', function() { $(this).trigger('blur'); });
  $('#moneda').on('change', function() { $(this).trigger('blur'); });
  $('#predeterminado').on('change', function() { $(this).trigger('blur'); });

});


function soloNumeros(e) {
    let key = e.which || e.keyCode;

    // Permitir: backspace, tab, delete, flechas
    if (
        key === 8  || // backspace
        key === 9  || // tab
        key === 46 || // delete
        (key >= 37 && key <= 40) // flechas
    ) {
        return true;
    }

    // Permitir solo números (0–9)
    if (key >= 48 && key <= 57) {
        return true;
    }

    e.preventDefault();
    return false;
}

$('#numero_cuenta, #cuenta_interbancaria').on('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
});

