
$("#guardar_registro_persona").on("click", function (e) { $("#submit-form-proveedor").submit(); });   




 lista_select2('/select2/obtener', '#distrito');

 lista_select2('/select2/Rolpersona', '#idtipo_persona');



 $("#distrito").select2({ theme: "bootstrap4", placeholder: "Seleccionar Distrito", allowClear: true, });
 $("#idtipo_persona").select2({ theme: "bootstrap4", placeholder: "Seleccionar", allowClear: true, });

 $("#tipo_entidad_sunat").select2({ theme: "bootstrap4", placeholder: "Seleccionar", allowClear: true, });
 $("#tipo_documento").select2({ theme: "bootstrap4", placeholder: "Seleccionar", allowClear: true, });

function show_hide_escenario(flag) {
  if (flag == 1) {            // Tabla principal
    $('#div-tabla-principal-persona').show();
    $(".btn-agregar-persona").show();
    $(".btn-cancelar").hide();
    
  } else if (flag == 2) {     // Detalle proyecto
    $('#div-tabla-principal-persona').hide();
    $(".btn-agregar-persona").hide();
    $(".btn-cancelar").show();
  } else if (flag == 3) {     //
  } else if (flag == 4) {
    
  }
}

$(document).ready(function() {
    // Cuando el valor del select cambia
    $('#distrito').change(function() {
        // Obtener el valor del atributo 'data-provincia' del option seleccionado
        var provincia = $(this).find('option:selected').data('provincia');
        var departamento = $(this).find('option:selected').data('departamento');

        $('#provincia').val(provincia);
        $('#departamento').val(departamento);

        console.log(provincia);
        

    });
});


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
  
  $.getJSON("/persona/tabla_principal", state, function(res){

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
     estado = r.estado == '1'?' <span class="text-center badge badge-success">Activado</span>':'Deshabilitado';
    $tb.append(`
      <tr class="fila-proyecto" data-id="${r.idpersona}">          
        <td class="py-1"> 
          <div class="btn-group btn-group-sm">
            <button class="btn btn-warning text-nowrap bnt-editar-proyecto" onclick="ver_editar_persona(${r.idpersona})" data-toggle="tooltip" data-original-title="Editar"><i class="ti ti-edit"></i></button>
            <button class="btn btn-danger text-nowrap bn-ver-proyecto" onclick="eliminar_persona(${r.idpersona}, '${r.nombre_razonsocial}')" data-toggle="tooltip" data-original-title="Ver"><i class="ti ti-trash"></i></button>
          </div>
        </td>
        <td class="py-1 text-center" >${String(r.idpersona).padStart(3, '0')}</td>
        <td class="py-1 text-nowrap" >${r.nombre_razonsocial ?? ''}</td>
        <td class="py-1" >${r.tipo_entidad_sunat ?? ''}</td>
        <td class="py-1" >${r.abreviatura ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.numero_documento ?? ''}</td>
        <td class="py-1 text-nowrap" >${r.celular ?? ''}</td>
        <td class="py-1 text-nowrap">${r.email ?? ''}</td>
        <td class="py-1 text-nowrap">${ r.direccion }</td>
        <td class="py-1 text-nowrap">${ r.tipoPersona }</td>
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
  e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state.page = Math.max(1, page); tabla_principal_cargar(); } 
});

// Eventos: ordenar al hacer clic en header
$("#tabla-proveedores thead").on("click", "th.sortable", function(){
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Ordenando...</td></tr>');
  const col = $(this).data("sort"); if (state.sort === col) { state.dir = (state.dir === 'asc') ? 'desc' : 'asc'; } else { state.sort = col;  state.dir  = 'asc'; } state.page = 1;    
  tabla_principal_cargar();
});

// Búsqueda con debounce
let t = null;
$("#buscar").on("input", function(){
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
  const val = $(this).val(); clearTimeout(t); t = setTimeout(function(){ state.q = val; state.page = 1; tabla_principal_cargar(); }, 300);
});

// Cambiar tamaño de página
$("#perPage").on("change", function(){
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
  state.per_page = parseInt($(this).val(), 10) || 20;  state.page = 1;
  tabla_principal_cargar();
});

// Carga inicial
tabla_principal_cargar();

$(".recargar-tabla-proyecto").on("click", function(){
  toastr_info('<i class="ti ti-checks"></i> Actualizando...', 'Los datos se estan actualizado', 500);
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');    

  tabla_principal_cargar();
});

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function limpiar_form_persona(){
  
  //Mostramos los Materiales
  $("#idpersona").val("");
  $("#nombre_razonsocial").val("");
  $("#email").val("");
  $("#celular").val("");
  $("#direccion").val("");
  $("#numero_documento").val(""); 
  $("#provincia").val("");
  $("#departamento").val("");

  $("#idtipo_persona").val("").trigger('change');
  $("#tipo_entidad_sunat").val("").trigger('change');
  $("#tipo_documento").val("").trigger('change');
  $("#distrito").val("").trigger('change');


  // Limpiamos las validaciones
  $(".form-control").removeClass('is-valid');
  $(".form-control").removeClass('is-invalid');
  $(".error.invalid-feedback").remove();
}

function ver_editar_persona(idpersona) {
  $("#cargando-1-formulario").hide();
  $("#cargando-2-formulario").show();
  limpiar_form_persona();
  $('#modal-agregar-proyecto').modal('show');
  $.getJSON(`persona/${idpersona}/ver-editar`, function (e) {
    if (e.status == true) {
      $("#idpersona").val(e.data.idpersona);
      $("#idbanco").val(e.data.idbanco);
      $("#idtipo_persona").val(e.data.idtipo_persona).trigger('change');
      $("#tipo_entidad_sunat").val(e.data.tipo_entidad_sunat).trigger('change');
      $("#tipo_documento").val(e.data.tipo_documento).trigger('change');
      $("#numero_documento").val(e.data.numero_documento);
      $("#nombre_razonsocial").val(e.data.nombre_razonsocial);
      $("#direccion").val(e.data.direccion);
      $("#celular").val(e.data.celular);
      $("#telefono_fijo").val(e.data.telefono_fijo);
      $("#email").val(e.data.email);
      $("#distrito").val(e.data.distrito).trigger('change');
      $("#provincia").val(e.data.provincia);
      $("#departamento").val(e.data.departamento);
      

      $("#cargando-1-formulario").show();
      $("#cargando-2-formulario").hide();
    } else {
      alert("No se encontró el persona");
    }
  }).fail(function (xhr) { ver_errores(xhr); });

}

function guardar_y_editar_persona(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-agregar-proveedor")[0]);

  var id = $("#idpersona").val();
  var url_editar_crear = '';
  if (id == '') {
    url_editar_crear =  `/persona/crear_persona` ;    
  } else {
    url_editar_crear = `/persona/editar_persona/${id}`;
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
          limpiar_form_persona();
          Swal.fire("Correcto!", "Proyecto guardado correctamente", "success");          
          $("#modal-agregar-proyecto").modal("hide");           
        }else{
          ver_errores(e);				 
        }
      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_persona").html('Guardar Cambios').removeClass('disabled');
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
      $("#guardar_registro_persona").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
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

function eliminar_persona(idpersona, nombres) {

  Swal.fire({
    title: "¿Está Seguro de eliminar el registro?",
    html: `<b class="text-danger"><del>${nombres}</del></b>`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, eliminar!",
  }).then((result) => {

    if (result.isConfirmed) {

      $.ajax({
        url: `/persona/eliminar_persona/${idpersona}`,
        type: "PUT",
        data: {
          _token: $('meta[name="csrf-token"]').attr('content') // necesario para PUT
        },
        success: function (e) {
          console.log(e);

          if (e.status === true) {
            Swal.fire("Eliminado!", "El registro ha sido eliminado.", "success");
            tabla_principal_cargar();
          } else {
            Swal.fire("Error!", e.message, "error");
          }
        },
        error: function (xhr) {
          Swal.fire("Error!", "Ocurrió un error en el servidor.", "error");
          console.log(xhr.responseText);
        }
      });

    }
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
    ver_editar_persona(idproyecto_select);
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

  // validamos el formulario  

  $('#tipo_entidad_sunat').on('change', function() { $(this).trigger('blur'); });
  $('#tipo_documento').on('change', function() { $(this).trigger('blur'); });

  $("#form-agregar-proveedor").validate({
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
      guardar_y_editar_persona(e);       
    },
  });

  $('#tipo_entidad_sunat').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#tipo_documento').rules('add', { required: true, messages: {  required: "Campo requerido" } });

});