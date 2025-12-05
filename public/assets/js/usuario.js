
$("#guardar_registro_usuario").on("click", function (e) { $("#submit-form-usuario").submit(); });   

// Cargar select2 de socios de negocio
 lista_select2('/select2/socionegocio', '#idpersona');

 $("#idpersona").select2({ theme: "bootstrap4", placeholder: "Seleccionar Socio Negocio", allowClear: true, });

$('#tipo_persona').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#tipo_documento').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });


function show_hide_escenario(flag) {
  if (flag == 1) {            // Tabla principal
    $('#div-tabla-principal-usuario').show();
    $(".btn-agregar-usuario").show();
    $(".btn-cancelar").hide();
    
  } else if (flag == 2) {     // Detalle proyecto
    $('#div-tabla-principal-usuario').hide();
    $(".btn-agregar-usuario").hide();
    $(".btn-cancelar").show();
    
  } else if (flag == 3) {     //
  } else if (flag == 4) {
    
  }
}

$(document).ready(function() {
    // Cuando el valor del select cambia
    $('#idpersona').change(function() {
        // Obtener el valor del atributo 'data-provincia' del option seleccionado
        var rol = $(this).find('option:selected').data('rol');
        $('#tipoPersona').val(rol);
        
    });

    permisos_usuario();
    
});


// Cargar permisos de usuario
function permisos_usuario(){
  
  $.getJSON("/usuario/permisos_crear", state, function(res){

    console.log(res.data);

    // Limpiar contenedor
    $('#permisos_usuario').html('');

    // Crear contenedor general de 2 columnas
    let html = `
        <div class="row">
            <div class="col-md-6" id="col1"></div>
            <div class="col-md-6" id="col2"></div>
        </div>
    `;

    $('#permisos_usuario').append(html);

    // Obtener grupos
    const grupos = Object.keys(res.data);

    // Dividir grupos a la mitad
    const mitad = Math.ceil(grupos.length / 2);

    const gruposCol1 = grupos.slice(0, mitad);
    const gruposCol2 = grupos.slice(mitad);

    // ---- COLUMNA 1 ----
    gruposCol1.forEach(grupo => {

        $('#col1').append(`
            <h6 class="fw-bold text-primary text-uppercase mt-3">${grupo}</h6>
            <div id="grupo_${grupo}"></div>
        `);

        res.data[grupo].forEach(p => {
            $(`#grupo_${grupo}`).append(`
                <div class="form-check mb-1">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        name="permisos[]" 
                        id="permiso_${p.idpermiso}"
                        value="${p.idpermiso}">
                    <label class="form-check-label" for="permiso_${p.idpermiso}">
                        ${p.escenario}
                    </label>
                </div>
            `);
        });

    });

    // ---- COLUMNA 2 ----
    gruposCol2.forEach(grupo => {

        $('#col2').append(`
            <h6 class="fw-bold text-primary text-uppercase mt-3">${grupo}</h6>
            <div id="grupo_${grupo}"></div>
        `);

        res.data[grupo].forEach(p => {
            $(`#grupo_${grupo}`).append(`
                <div class="form-check mb-1">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        name="permisos[]" 
                        id="permiso_${p.idpermiso}"
                        value="${p.idpermiso}">
                    <label class="form-check-label" for="permiso_${p.idpermiso}">
                        ${p.escenario}
                    </label>
                </div>
            `);
        });

    });

  }).fail(function (xhr) { ver_errores(xhr); });
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
function tabla_principal_usuario(){
  
  $.getJSON("/usuario/tabla_principal", state, function(res){

    console.log(res.data);
    
    renderFilas(res.data);
    renderPaginacion(res.current_page, res.last_page);
    marcarOrden(state.sort, state.dir);
  }).fail(function (xhr) { ver_errores(xhr); });
}

// Render filas de la tabla
function renderFilas(rows){
  const $tb = $("#tabla-proveedores tbody").empty();
  if (!rows || rows.length === 0){
    $tb.append('<tr><td colspan="15" class="text-center text-muted">Sin resultados</td></tr>');
    return;
  }
  rows.forEach(r => {
    estado = r.estado_trash == '1'?' <span class="text-center badge badge-success">Activado</span>':'Deshabilitado';
    $tb.append(`
      <tr class="fila-proyecto" data-id="${r.id}">          
        <td class="py-1"> 
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning text-nowrap bnt-editar-proyecto" onclick="ver_editar_proyecto(${r.id})" data-toggle="tooltip" data-original-title="Editar"><i class="ti ti-edit"></i></button>
            <button class="btn btn-info text-nowrap bn-ver-proyecto" onclick="ver_detalle_proyecto(${r.id})" data-toggle="tooltip" data-original-title="Ver"><i class="ti ti-eye-cog"></i></button>
          </div>
        </td>
        <td class="py-1 text-center" >${r.id ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.usuario ?? ''}</td>
        <td class="py-1" >${r.nombre_razonsocial ?? ''}</td>
        <td class="py-1" >${r.abreviatura ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.numero_documento ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.tipo_entidad_sunat ?? ''}</td>
        <td class="py-1 text-nowrap">${r.tipo_persona ?? ''}</td>
        <td class="py-1 text-nowrap">${ estado }</td>
        
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
  $("#tabla-proveedores thead th.sortable").each(function(){ const $th = $(this);  const c = $th.data('sort');  $th.removeClass('asc desc'); if (c === col) $th.addClass(dir);  });
}

// Eventos: click en paginación
$("#paginacion").on("click", "a.page-link", function(e){  
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state.page = Math.max(1, page); tabla_principal_usuario(); } 
});

// Eventos: ordenar al hacer clic en header
$("#tabla-proveedores thead").on("click", "th.sortable", function(){
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Ordenando...</td></tr>');
  const col = $(this).data("sort"); if (state.sort === col) { state.dir = (state.dir === 'asc') ? 'desc' : 'asc'; } else { state.sort = col;  state.dir  = 'asc'; } state.page = 1;    
  tabla_principal_usuario();
});

// Búsqueda con debounce
let t = null;
$("#buscar").on("input", function(){
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  const val = $(this).val(); clearTimeout(t); t = setTimeout(function(){ state.q = val; state.page = 1; tabla_principal_usuario(); }, 300);
});

// Cambiar tamaño de página
$("#perPage").on("change", function(){
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
  state.per_page = parseInt($(this).val(), 10) || 20;  state.page = 1;
  tabla_principal_usuario();
});

// Carga inicial
tabla_principal_usuario();

$(".recargar-tabla-proyecto").on("click", function(){
  toastr_info('<i class="ti ti-checks"></i> Actualizando...', 'Los datos se estan actualizado', 500);
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');    

  tabla_principal_usuario();
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
  $('#modal-agregar-usuario').modal('show');
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

function guardar_y_editar_proveedor(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-agregar-usuario")[0]);

  var id = $("#id").val();
  var url_editar_crear = '';
  if (id == '') {
    url_editar_crear =  `/persona/crear_usuario` ;    
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
          tabla_principal_usuario();
          limpiar_form_proyecto();
          Swal.fire("Correcto!", "Proyecto guardado correctamente", "success");          
          $("#modal-agregar-usuario").modal("hide");           
        }else{
          ver_errores(e);				 
        }
      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_usuario").html('Guardar Cambios').removeClass('disabled');
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
      $("#guardar_registro_usuario").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
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

  $('#tipo_entidad_sunat').on('change', function() { $(this).trigger('blur'); });
  $('#tipo_documento').on('change', function() { $(this).trigger('blur'); });

  $("#form-agregar-usuario").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {

      tipo_entidad_sunat:    { required: true, },
      tipo_documento:  { required: true, },
      numero_documento:   { required: true, },
      direccion:       { required: true, },
      nombre_razonsocial: { required: true, },
      celular:        { required: true, },
      email:           { required: true, },      
    },
    messages: {
      tipo_entidad_sunat:    { required: "Campo requerido.", },
      tipo_documento:  { required: "Campo requerido.", },
      numero_documento:   { required: "Campo requerido.", },
      direccion:       { required: "Campo requerido.", },
      nombre_razonsocial: { required: "Campo requerido.", },
      celular:        { required: "Campo requerido.", },
      email:           { required: "Campo requerido.", },
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
      guardar_y_editar_proveedor(e);       
    },
  });

  $('#tipo_entidad_sunat').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#tipo_documento').rules('add', { required: true, messages: {  required: "Campo requerido" } });

});