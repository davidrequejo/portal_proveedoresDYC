
$("#guardar_registro_proyecto").on("click", function (e) { $("#submit-form-proyecto").submit(); });   

// lista_select2("../ajax/ajax_general.php?op=select2EmpresaACargo", '#empresa_acargo', null);

$('#idempresa').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#idsocio_negocio').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });


function show_hide_escenario(flag) {
  if (flag == 1) {            // Tabla principal
    $('#div-tabla-principal-proyecto').show();
    $("#div-ver-detalle-proyecto").hide();
    $(".btn-agregar-proyecto").show();
    $(".btn-cancelar").hide();
    
  } else if (flag == 2) {     // Detalle proyecto
    $('#div-tabla-principal-proyecto').hide();
    $("#div-ver-detalle-proyecto").show();
    $(".btn-agregar-proyecto").hide();
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
function tabla_principal_cargar(){
  
  $.getJSON("/proyectos/tabla_principal", state, function(res){
    renderFilas(res.data);
    renderPaginacion(res.current_page, res.last_page);
    marcarOrden(state.sort, state.dir);
  }).fail(function (xhr) { ver_errores(xhr); });
}

// Render filas de la tabla
function renderFilas(rows){
  const $tb = $("#tabla-proyectos tbody").empty();
  if (!rows || rows.length === 0){
    $tb.append('<tr><td colspan="15" class="text-center text-muted">Sin resultados</td></tr>');
    return;
  }
  rows.forEach(r => {
    $tb.append(`
      <tr class="fila-proyecto" data-id="${r.idproyecto}">          
        <td class="py-1"> 
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning text-nowrap bnt-editar-proyecto" onclick="ver_editar_proyecto(${r.idproyecto})" data-toggle="tooltip" data-original-title="Editar"><i class="ti ti-edit"></i></button>
            <button class="btn btn-info text-nowrap bn-ver-proyecto" onclick="ver_detalle_proyecto(${r.idproyecto})" data-toggle="tooltip" data-original-title="Ver"><i class="ti ti-eye-cog"></i></button>
          </div>
        </td>
        <td class="py-1 text-nowrap" >${r.codigo ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.descripcion ?? ''}</td>
        <td class="py-1" >${r.cliente ?? ''}</td>
        <td class="py-1" >${r.empresa ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.fecha_inicio_dmy ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.fecha_fin_dmy ?? ''}</td>
        <td class="py-1 text-right">${ formato_miles( (r.total_presupuesto ?? 0) )}</td>
        <td class="py-1 text-nowrap">${ r.direccion }</td>
        <td class="py-1 text-nowrap">${ r.ubicacion }</td>
        
      </tr>
    `);
    $('[data-toggle="tooltip"]').tooltip(); 
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
  $("#tabla-proyectos thead th.sortable").each(function(){ const $th = $(this);  const c = $th.data('sort');  $th.removeClass('asc desc'); if (c === col) $th.addClass(dir);  });
}

// Eventos: click en paginación
$("#paginacion").on("click", "a.page-link", function(e){  
  $("#tabla-proyectos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state.page = Math.max(1, page); tabla_principal_cargar(); } 
});

// Eventos: ordenar al hacer clic en header
$("#tabla-proyectos thead").on("click", "th.sortable", function(){
  $("#tabla-proyectos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Ordenando...</td></tr>');
  const col = $(this).data("sort"); if (state.sort === col) { state.dir = (state.dir === 'asc') ? 'desc' : 'asc'; } else { state.sort = col;  state.dir  = 'asc'; } state.page = 1;    
  tabla_principal_cargar();
});

// Búsqueda con debounce
let t = null;
$("#buscar").on("input", function(){
  $("#tabla-proyectos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  const val = $(this).val(); clearTimeout(t); t = setTimeout(function(){ state.q = val; state.page = 1; tabla_principal_cargar(); }, 300);
});

// Cambiar tamaño de página
$("#perPage").on("change", function(){
  $("#tabla-proyectos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
  state.per_page = parseInt($(this).val(), 10) || 20;  state.page = 1;
  tabla_principal_cargar();
});

// Carga inicial
tabla_principal_cargar();

$(".recargar-tabla-proyecto").on("click", function(){
  toastr_info('<i class="ti ti-checks"></i> Actualizando...', 'Los datos se estan actualizado', 500);
  $("#tabla-proyectos tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');    

  tabla_principal_cargar();
});

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function limpiar_form_proyecto(){
  
  //Mostramos los Materiales
  $("#idproyecto").val("");
  $("#codigo").val("");
  $("#descripcion").val("");
  $("#direccion").val("");
  $("#ubicacion").val("");
  $("#fecha_inicio").val("");
  $("#fecha_fin").val("");

  $("#idempresa").val("").trigger('change');
  $("#idsocio_negocio").val("").trigger('change');

  // Limpiamos las validaciones
  $(".form-control").removeClass('is-valid');
  $(".form-control").removeClass('is-invalid');
  $(".error.invalid-feedback").remove();
}

function ver_editar_proyecto(idproyecto) {
  $("#cargando-1-formulario").hide();
  $("#cargando-2-formulario").show();
  limpiar_form_proyecto();
  $('#modal-agregar-proyecto').modal('show');
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

function guardar_y_editar_proyecto(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-agregar-proyecto")[0]);

  var id = $("#idproyecto").val();
  var url_editar_crear = '';
  if (id == '') {
    url_editar_crear =  `/proyectos/crear_proyecto` ;    
  } else {
    url_editar_crear = `/proyectos/editar_proyecto/${id}`;
    formData.append('_method', 'PUT'); // spoof para Laravel
  }
  

  $.ajax({
    url: url_editar_crear,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (e) {
      try {        
        if (e.status == true) {          
          tabla_principal_cargar();
          limpiar_form_proyecto();
          Swal.fire("Correcto!", "Proyecto guardado correctamente", "success");          
          $("#modal-agregar-proyecto").modal("hide");           
        }else{
          ver_errores(e);				 
        }
      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_proyecto").html('Guardar Cambios').removeClass('disabled');
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
      $("#guardar_registro_proyecto").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
      $("#barra_progress_proyecto").css({ width: "0%",  });
      $("#barra_progress_proyecto").text("0%");
    },
    complete: function () {
      $("#barra_progress_proyecto").css({ width: "0%", });
      $("#barra_progress_proyecto").text("0%");
    },
    error: function (jqXhr) { ver_errores(jqXhr); },
  });
}

function empezar_proyecto(idproyecto, nombre_proyecto ) {
  crud_simple_alerta(
    '../ajax/proyecto.php?op=empezar_proyecto', 
    idproyecto, 
    '¿Está Seguro de  Empezar  el proyecto ?', 
    `<b class="text-success">${nombre_proyecto}</b> <br> Tendras acceso a agregar o editar: provedores, trabajadores!`, 
    'Si, Empezar!',
    function(){ Swal.fire("En curso!", "Tu proyecto esta en curso.", "success"); },
    function(){ tabla.ajax.reload(null, false);  box_proyecto();}
  );  
}
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   DETALLE PROYECTO                                                        ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
function ver_detalle_proyecto(idproyecto) {
  show_hide_escenario(2);
  $(".span-proyecto-charge").addClass('skeleton');
  $.getJSON(`/proyectos/detalle-html/${idproyecto}`, function (e) {
    if (e.status == true) {
     $("#div-ver-detalle-proyecto").html(e.data);
    } else {
      alert("No se encontró el proyecto");
    }
    $(".span-proyecto-charge").removeClass('skeleton');
  }).fail(function (xhr) { ver_errores(xhr);  });
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
    ver_editar_proyecto(idproyecto_select);
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
  $('#modal-agregar-presupuesto').modal('show');
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

  // validamos el formulario  

  $('#fecha_pago_obrero').on('change', function() { $(this).trigger('blur'); });
  $('#fecha_valorizacion').on('change', function() { $(this).trigger('blur'); });
  $('#empresa_acargo').on('change', function() { $(this).trigger('blur'); });

  $("#form-agregar-proyecto").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {
      codigo:           { required: true, minlength: 5, maxlength: 8, },
      descripcion:      { required: true, minlength: 4, maxlength: 300 },
      direccion:        { required: true, minlength: 4, maxlength: 300 },
      ubicacion:        { required: true, minlength: 4, maxlength: 300 },
      fecha_inicio:     { required: true, },
      fecha_fin:        { required: true, },      
    },
    messages: {
      codigo:           { required: "Campo requerido.", minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      descripcion:      { required: "Campo requerido.", minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      direccion:        { required: "Campo requerido.", minlength: "MÍNIMO {0} caracteres.", maxlength: "MÁXIMO {0} caracteres.", },
      ubicacion:        { required: "Campo requerido.", },
      fecha_inicio:     { required: "Campo requerido.", },
      fecha_fin:        { required: "Campo requerido.", },
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
      guardar_y_editar_proyecto(e);       
    },
  });

  $('#fecha_pago_obrero').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#fecha_valorizacion').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#empresa_acargo').rules('add', { required: true, messages: {  required: "Campo requerido" } });

});