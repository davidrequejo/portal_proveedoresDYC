const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
var idpersona_tipo = null;
var idtipoestandarproveedor_tipo = null;
var nombre_razonsocial_tipo = null;
var email_proveedor_env_correo = null;

//-------------------- Inicializaciones ------------------------------------
var idfechaperso_homol_edit = null;
var  descrp_homol_edit = null;

 $('#fecha_inicio_periodo').val(new Date().toISOString().slice(0,10));


$("#guardar_registro_proveedor").on("click", function (e) { $("#submit-form-proveedor").submit(); });   
$("#guardar_registro_add_homologacion").on("click", function (e) { $("#submit-form-add_homologacion").submit(); });
$("#guardar_registro_actualizar_estado").on("click", function (e) { $("#submit-form-actualizar_estado").submit(); });

// lista_select2("../ajax/ajax_general.php?op=select2EmpresaACargo", '#empresa_acargo', null);

lista_select2(`${BASE_URL}/select2/obtener`, '#distrito'); 
lista_select2(`${BASE_URL}/select2/tipoestandar`, '#idtipoestandarproveedor');


$("#distrito").select2({ theme: "bootstrap4", placeholder: "Seleccionar Distrito", allowClear: true, });
$("#idtipoestandarproveedor").select2({ theme: "bootstrap4", placeholder: "Seleccionar", allowClear: true, });

$('#tipo_persona').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#tipo_documento').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#idsocio_negocio').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#tipo_entidad_sunat').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });
$('#estado_documentos_update').select2({ theme: "bootstrap4", placeholder: "Selecione", allowClear: true });

$("#estado_documentos_update").val("").trigger('change');

function show_hide_escenario(flag) {
  if (flag == 1) {            // Tabla principal
    $('#div-tabla-principal-proyecto').show();
    $("#div-ver-detalle-documentos").hide();
    $(".btn-agregar-proyecto").show();
    $(".btn-cancelar").hide();

    $(".Nombre_inicial").html(`Proveedores`);
    
  } else if (flag == 2) {     // Detalle proyecto
    $('#div-tabla-principal-proyecto').hide();
    $("#div-ver-detalle-documentos").show();
    $(".btn-agregar-proyecto").hide();
    $(".btn-cancelar").show();
  } else if (flag == 3) {     //
  } else if (flag == 4) {
    
  }
}

//**activamso el bton si el estado de la sunat es favorable */
$('#estado_sunat').on('change', function () {
  const estado = $(this).val();
  $('#guardar_registro_proveedor').toggle(estado === 'ACTIVO');
});


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

document.getElementById("btn_generar_credenciales").addEventListener("click", function () {

    let razon = document.getElementById("nombre_razonsocial")?.value || "";
    let ruc = document.getElementById("numero_documento")?.value || "";

    if (razon.trim() === "") { toastr_warning('Primero completa el campo de Nombre / Razón Social', 'Campo requerido');     
        return;
    }

    // --- GENERAR USUARIO DESDE NOMBRE ---
    let usuario = razon
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // quitar tildes
        .replace(/[^a-z0-9]/g, "")                       // quitar caracteres raros
        .substring(0, 12);                               // limitar a 12 chars

    // si está vacío, usar RUC
    if (usuario.trim() === "") {
        usuario = "user" + ruc.slice(-4);
    }

    // --- GENERAR CONTRASEÑA DESDE RUC + RANDOM ---
    let random = Math.floor(100 + Math.random() * 900); // 3 dígitos
    let password = ruc.slice(-4) + random;

    if (usuario!=null && password!=null) {
      // Colocar valores en inputs
      document.getElementById("usuario_portal").value = usuario;
      document.getElementById("clave_portal").value = password;

      toastr_success('Usuario y contraseña generado conrrectamente');

    }

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
  $("#tabla-proveedores tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
  
  $.getJSON(`${BASE_URL}/proveedor/tabla_principal`, state, function(res){

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

    $tb.append(`
      <tr class="fila-proyecto" data-id="${r.idpersona}">          
        <td class="py-1"> 
          <div class="btn-group btn-group-sm">
            <button class="btn btn-xs text-nowrap bnt-editar-proyecto" onclick="ver_editar_proveedor(${r.idpersona})" data-toggle="tooltip" data-original-title="Editar"> <i class="fas fa-pencil-alt color_icon_opt"></i></button>
            <button class="btn btn-xs text-nowrap bn-ver-proyecto" onclick="lista_homologaciones(${r.idpersona}, '${r.nombre_razonsocial ?? ''}','${r.email ?? ''}')" data-toggle="tooltip" data-original-title="Homologaciones"><i class="fas fa-folder fa-0 color_icon_opt"></i></button>
            <button class="btn btn-xs text-nowrap bn-ver-proyecto" onclick="eliminar_proveedor(${r.idpersona}, '${r.nombre_razonsocial ?? ''}')" data-toggle="tooltip" data-original-title="Eliminar"><i class="fas fa-trash color_icon_opt"></i></button>
          </div>
        </td>
        <td class="py-1 text-center"> ${String('000').padStart(3, '0')} </td>
        <td class="py-1 text-nowrap">${r.nombre_razonsocial ?? ''}</td>
        <td class="py-1" >${r.tipo_entidad_sunat ?? ''}</td>
        <td class="py-1" >${r.abreviatura ?? ''}</td>
        <td class="py-1 text-nowrap">${r.numero_documento ?? ''}</td>
        <td class="py-1 text-nowrap">${r.celular ?? ''}</td>
        <td class="py-1 text-nowrap">${r.email ?? ''}</td>
        <td class="py-1" style="max-width: 220px; white-space: normal; overflow-wrap: anywhere; word-break: break-word;">${ r.direccion } </td>
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
// ═══════                                       S E C C I O N   C R U D   P R O V E E D O R                                                        ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function limpiar_form_proveedor(){
  
  //Mostramos los Materiales
  $("#fecha_inicio_periodo").val("");
  $("#fecha_fin_periodo").val("");
  $("#idpersona").val("");
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

function ver_editar_proveedor(idpersona) {
  $("#cargando-1-formulario").hide();
  $("#cargando-2-formulario").show();
  limpiar_form_proveedor();
  $('#modal-agregar-proveedor').modal('show');
  $.getJSON(`${BASE_URL}/proveedor/${idpersona}/mostrar_proveedor`, function (e) {
    console.log(e.data);

    if (e.status == true) {

      $("#fecha_inicio_periodo").val(e.data.proveedor.fecha_inicio_periodo);
      $("#fecha_fin_periodo").val(e.data.proveedor.fecha_fin_periodo);

      $("#idpersona").val(e.data.proveedor.idpersona);

      $("#idtipo_persona").val(e.data.proveedor.idtipo_persona);
      $("#tipo_entidad_sunat").val(e.data.proveedor.tipo_entidad_sunat).trigger('change');
      $("#tipo_documento").val(e.data.proveedor.tipo_documento).trigger('change');
      $("#numero_documento").val(e.data.proveedor.numero_documento);
      $("#nombre_razonsocial").val(e.data.proveedor.nombre_razonsocial);
      $("#nombre_persona_natural").val(e.data.proveedor.nombre_persona_natural);
      $("#apellido_paterno_per_natural").val(e.data.proveedor.apellido_paterno_per_natural);
      $("#apellido_materno_per_natural").val(e.data.proveedor.apellido_materno_per_natural);
      $("#celular").val(e.data.proveedor.celular);
      $("#direccion").val(e.data.proveedor.direccion);
      $("#distrito").val(e.data.proveedor.distrito).trigger('change');
      $("#provincia").val(e.data.proveedor.provincia);
      $("#departamento").val(e.data.proveedor.departamento);
      $("#email").val(e.data.proveedor.email);

      if (e.data.usuario != null) {
        $("#id").val(e.data.usuario.id);
        $("#usuario_portal").val(e.data.usuario.email);
        $("#clave_portal").val(e.data.usuario.clave_portal);
      }else{
        $("#id").val('');
        $("#usuario_portal").val('');
        $("#clave_portal").val('');
      }

      $("#cargando-1-formulario").show();
      $("#cargando-2-formulario").hide();
    } else {
      alert("No se encontró el proyecto");
    }
  }).fail(function (xhr) { ver_errores(xhr); });

}

function guardar_y_editar_proveedor(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-agregar-proveedor")[0]);

  var id = $("#idpersona").val();
  var url_editar_crear = '';
  if (id == '') {
    url_editar_crear =  `${BASE_URL}/proveedor/crear_proveedor` ;    
  } else {
    url_editar_crear = `${BASE_URL}/proveedor/editar_proveedor/${id}`;
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
          limpiar_form_proveedor();
          Swal.fire("Correcto!", "Proyecto guardado correctamente", "success");          
          $("#modal-agregar-proveedor").modal("hide");           
        }else{
          ver_errores(e);				 
        }
      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_proveedor").html('Guardar Cambios').removeClass('disabled');
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
      $("#guardar_registro_proveedor").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
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

function eliminar_proveedor(id, nombres) {

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
        url: `${BASE_URL}/proveedor/eliminar_proveedor/${id}`,
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

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   HOMOLOGACIONES                                                    ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function limpiar_homologacion() {
  $("#idpersona_facha_homologacion").val("");
  $("#idtipoestandarproveedor").val("").trigger('change');
  $("#descripcion_homologacion").val("Periodo");
  $("#fecha_fin_periodo").val("");

}

function lista_homologaciones(idpersona, nombre_razonsocial, email) { 

  idpersona_tipo = idpersona;
  nombre_razonsocial_tipo = nombre_razonsocial;
  email_proveedor_env_correo = email;


  $("#idproveedor").val(idpersona);
  $(".Nombre_inicial").html(`Proveedor <span class="text-principal hove-negrita"> : ${nombre_razonsocial} </span>`);
  
  show_hide_escenario(2);

    //$("#titulo-detalle-proyecto").html(`Documentos del Proveedor: <b class="text-info">${nombre_razonsocial}</b>`);
  $(".tabla-list-homolog").html('<tr><td colspan="8" class="text-center text-muted">Ninguno</td></tr>');

   var cont =1;

  $.getJSON(`${BASE_URL}/homologacion/tabla_periodo_h_principal`,{ idpersona: idpersona}, function (e) {
    if (e.status == true) {
      $(".tabla-list-homolog").empty('');
      e.data.forEach(r => {
      
        $(".tabla-list-homolog").append(`
          <tr>
            <td class="py-1"> ${String(cont++).padStart(3, '0')} </td>
            <td class="py-1"> 
              <div class="btn-group btn-group-sm">
                <button class="btn btn-xs text-nowrap bnt-editar-proyecto" onclick="ver_editar_periodo_h(${r.idpersona_facha_homologacion})" data-toggle="tooltip" data-original-title="Editar"> <i class="fas fa-pencil-alt color_icon_opt"></i></button>
                <button class="btn btn-xs text-nowrap bn-ver-proyecto" onclick="eliminar_periodo_h(${r.idpersona_facha_homologacion}, '${r.descripcion ?? ''}')" data-toggle="tooltip" data-original-title="Eliminar"><i class="fas fa-trash color_icon_opt"></i></button>
              </div>
            </td>
            <td class="py-1 text-nowrap" >${r.descripcion} </td>
            <td class="py-1 text-nowrap" >${r.fecha_inicio}</td>
            <td class="py-1 text-nowrap" >${r.fecha_fin}</td>
            <td class="py-1 text-nowrap" >Tipo Estandar 5</td>
            <td class="py-1 text-center" style="cursor:pointer">
              <button class="btn btn-xs text-nowrap" onclick="ver_documentos_x_homologacion(${r.idpersona_facha_homologacion},'${r.descripcion}')" data-toggle="tooltip" data-original-title="Ver Detalle"><i class="fas fa-eye fa-lg color_icon_opt"></i></button>
            </td>
          </tr>
        `);
        $('[data-toggle="tooltip"]').tooltip(); 
      });
    // $("#div-ver-detalle-documentos").html(e.data);
    } else {
      alert("No se encontró el proyecto");
    }
  }).fail(function (xhr) { ver_errores(xhr);  });

}



function guardar_y_editar_homoloacion(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-add_homologacion")[0]);
  var id = $("#idpersona_facha_homologacion").val();
  var url_editar_crear = '';
  if (id == '') {
    url_editar_crear =  `${BASE_URL}/homologacion/crear_periodo_homologacion` ;    
  } else {
    url_editar_crear = `${BASE_URL}/homologacion/editar_periodo_homologacion/${id}`;
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

          limpiar_homologacion();
          lista_homologaciones(idpersona_tipo, nombre_razonsocial_tipo, email_proveedor_env_correo);
          Swal.fire("Correcto!", "Periodo de homologación creado correctamente", "success");          
          $("#modal_agregar_homologacion").modal("hide");           
        }else{
          ver_errores(e);				 
        }
      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_actualizar_estado").html('Guardar Cambios').removeClass('disabled');
    },
    xhr: function () {
      var xhr = new window.XMLHttpRequest();
      xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
          var percentComplete = (evt.loaded / evt.total)*100; /*console.log(percentComplete + '%');*/
          $("#barra_progress_act_est").css({"width": percentComplete+'%'}); $("#barra_progress_act_est").text(percentComplete.toFixed(2)+" %");
        }
      }, false);
      return xhr;
    },
    beforeSend: function () {
      $("#guardar_registro_actualizar_estado").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
      $("#barra_progress_act_est").css({ width: "0%",  });
      $("#barra_progress_act_est").text("0%");
    },
    complete: function () {
      $("#barra_progress_act_est").css({ width: "0%", });
      $("#barra_progress_act_est").text("0%");
    },
    error: function (jqXhr) { ver_errores(jqXhr); },
  });
}

function ver_editar_periodo_h(id) {
  $("#cargando-5-formulario-homologacion").hide();
  $("#cargando-6-formulario-homologacion").show();
  limpiar_homologacion();
  $('#modal_agregar_homologacion').modal('show');
  $.getJSON(`${BASE_URL}/homologacion/${id}/mostrar_periodo_homologacion`, function (e) {
    console.log(e.data);
    if (e.status == true) {

      $("#idpersona_facha_homologacion").val(e.data.data_homolog.idpersona_facha_homologacion);
      $("#idtipoestandarproveedor").val(e.data.idtipoestandarproveedor).trigger('change');
      $("#descripcion_homologacion").val(e.data.data_homolog.descripcion);
      $("#fecha_inicio_periodo").val(e.data.data_homolog.fecha_inicio);
      $("#fecha_fin_periodo").val(e.data.data_homolog.fecha_fin);

      $("#cargando-5-formulario").show();
      $("#cargando-6-formulario").hide();
    } else {
      alert("No se encontró el datos");
    }
  }).fail(function (xhr) { ver_errores(xhr); });

}

function ver_documentos_x_homologacion(id, descripcion) {
    idfechaperso_homol_edit = id;
    descrp_homol_edit = descripcion;

  $(".text_nombre_periodo_homol").text(`${descripcion}`);
  $(".tbl_lista_documentos").html('<tr><td colspan="10" class="text-center text-muted">Ninguno</td></tr>');

  $(".mostrar_documento_pdf").hide();
  $(".tbl_lista_documento_hmolog").show();

  var cont =1;

  $.getJSON(`${BASE_URL}/homologacion/listar_docs_xperiodo_xproveedor`,{  idperiodo_homologacion: id, idpersona:'' }, function (e) {
   if (e.status == true) {
    console.log(e.data);
    
      $(".tbl_lista_documentos").empty('');
      e.data.forEach(r => {

          let estadoHtml = '';
          const isPendiente = (r.estado_revision == 'Pendiente');

          switch (r.estado_revision) {
              case 'Registrado': estadoHtml = `<span class="badge bg-warning text-dark">Registrado</span>`; break;

              case 'Observado': estadoHtml = `<span class="badge bg-orange text-white">Observado</span>`; break;

              case 'Aprobado': estadoHtml = `<span class="badge bg-success">Aprobado</span>`; break;

              case 'Rechazado': estadoHtml = `<span class="badge bg-dark">Rechazado</span>`; break;

              default: estadoHtml = `<span class="badge bg-danger">Pendiente.</span>`; 
          }
      
        $(".tbl_lista_documentos").append(`
          <tr>
            <td class="py-1"> ${String(cont++).padStart(3, '0')} </td>
            <td class="py-1 text-nowrap" ><i class="fas fa-file-pdf fa-lg color_icon_opt"></i> ${r.descripcion ?? ''}</td>
            <td class="py-1 text-nowrap" >${estadoHtml}</td>
            <td class="py-1 text-center" style="cursor:pointer">
                ${r.archivo
                    ? `<a class="text-muted"
                         onclick="ver_documento_proveedor('${BASE_URL}/${r.archivo}','${r.descripcion}')">
                        <i class="fas fa-search"></i>
                       </a>`
                    : `<a class="text-muted"
                         onclick="ver_documento_proveedor('${BASE_URL}/${r.archivo}','${r.descripcion}')">
                        <i class="fas fa-search"></i>
                       </a>—`
                }
            </td>
            <td class="py-1 text-center" >
              ${isPendiente
                  ? `<a class="btn  btn-sm disabled"
                      style="pointer-events:none; opacity:0.6;">
                      <i class="fas fa-pencil-alt "></i>
                    </a>`
                  : `<a class="btn btn-sm"
                      onclick="actualizar_estado_documento(${r.iddocsproveedortipoestandar}, ${r.idpersona},'${r.descripcion ?? ''}')">
                      <i class="fas fa-pencil-alt color_icon_opt"></i>
                    </a>`
              }
            </td>
          </tr>
        `);
         
      });
    // $("#div-ver-detalle-documentos").html(e.data);
    $('[data-toggle="tooltip"]').tooltip();
    } else {
      alert("No se encontró el proyecto");
    }
  }).fail(function (xhr) { ver_errores(xhr);  });
}


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   ACTUALIZAR ESTADO                                                    ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════




function ver_documento_proveedor(ruta_documento, nombre_documento) {

  $("#modal_ver_documento").modal("show");
  $(".nombre_doc_edit").text(`${nombre_documento}`);

  $(".nombre_documento_pdf").html(`<b class="text-principal">${nombre_documento}</b>`);

  $('.ver_documento_modal').html(``); 

  var doc_html = doc_view_extencion(ruta_documento, '', '', '100%', '100%' );

  $('.ver_documento_modal').html(doc_html);

}

function actualizar_estado_documento(id, idpersona, nombre_documento) { 

  $("#iddocsproveedortipoestandar").val(id);
  $("#idpersonadoc").val(idpersona);

  $("#inputnombre_razonsocial_tipo").val(nombre_razonsocial_tipo);
  $("#inputemail_proveedor_env_correo").val(email_proveedor_env_correo);



  $(".nombre_doc_edit").text(`${nombre_documento}`);
  $("#modal-actualizar-estado").modal("show");
  
}

function guardar_y_editar_act_estado(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-actualizar-estado")[0]);
  var id = $("#iddocsproveedortipoestandar").val();
  var url_editar_crear = '';
  if (id == '') {
    url_editar_crear =  `` ;    
  } else {
    url_editar_crear = `${BASE_URL}/homologacion/actualizar_estado_doc_estandar/${id}`;
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
          ver_documentos_x_homologacion(idfechaperso_homol_edit, descrp_homol_edit) ;
          Swal.fire("Correcto!", "Actualizado correctamente", "success");          
          $("#modal-actualizar-estado").modal("hide");           
        }else{
          ver_errores(e);				 
        }
      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_actualizar_estado").html('Guardar Cambios').removeClass('disabled');
    },
    xhr: function () {
      var xhr = new window.XMLHttpRequest();
      xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
          var percentComplete = (evt.loaded / evt.total)*100; /*console.log(percentComplete + '%');*/
          $("#barra_progress_act_est").css({"width": percentComplete+'%'}); $("#barra_progress_act_est").text(percentComplete.toFixed(2)+" %");
        }
      }, false);
      return xhr;
    },
    beforeSend: function () {
      $("#guardar_registro_actualizar_estado").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
      $("#barra_progress_act_est").css({ width: "0%",  });
      $("#barra_progress_act_est").text("0%");
    },
    complete: function () {
      $("#barra_progress_act_est").css({ width: "0%", });
      $("#barra_progress_act_est").text("0%");
    },
    error: function (jqXhr) { ver_errores(jqXhr); },
  });
}

// ══════════════════════════════════════════════════════════════════════════════════
// ══ S E C C I O N   C L I C K   D E R E C H O   T A B L A                                              ══

let idpersona_select = null;
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
  idpersona_select = $(this).data("id");

  $("#menu-contextual-proyecto").css({ top: e.pageY + "px", left: e.pageX + "px", }).show();
});

// Opciones del menú contextual
$("#opcion-p-editar").on("click", function (e) {
  e.preventDefault();
  if (idpersona_select) {
    ver_editar_proveedor(idpersona_select);
  }
});

$("#opcion-p-ver-detalle").on("click", function (e) {
  e.preventDefault();
  if (idpersona_select) {
    ver_detalle_proyecto(idpersona_select);
  }
});

$("#opcion-p-eliminar").on("click", function (e) {
  e.preventDefault();
  if (idpersona_select) {
    toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
  }
});

$("#opcion-p-enviar-terminado").on("click", function (e) {
  e.preventDefault();
  if (idpersona_select) {
    toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
  }
});

$("#opcion-p-enviar-papelera").on("click", function (e) {
  e.preventDefault();
  if (idpersona_select) {
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
  $('#idtipoestandarproveedor').on('change', function() { $(this).trigger('blur'); });
  $('#tipo_entidad_sunat').on('change', function() { $(this).trigger('blur'); });
  $('#tipo_documento').on('change', function() { $(this).trigger('blur'); });
  $('#estado_documentos_update').on('change', function() { $(this).trigger('blur'); });

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
      guardar_y_editar_proveedor(e);       
    },
  });

  $("#form-add_homologacion").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {

      idtipoestandarproveedor:    { required: true, },     
      descripcion_homologacion:    { required: true, },   
      fecha_fin_periodo:    { required: true, },  
      fecha_inicio_periodo:    { required: true, },  
    },
    messages: {
      idtipoestandarproveedor:    { required: "Campo requerido.", },
      descripcion_homologacion:    { required: "Campo requerido.", },
      fecha_fin_periodo:    { required: "Campo requerido.", },
      fecha_inicio_periodo:    { required: "Campo requerido.", },
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
      guardar_y_editar_homoloacion(e);       
    },
  });

  $("#form-actualizar-estado").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {

      estado_documentos_update:    { required: true, },     
    },
    messages: {
      estado_documentos_update:    { required: "Campo requerido.", },
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
      guardar_y_editar_act_estado(e);       
    },
  });
  
  $('#idtipoestandarproveedor').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#tipo_entidad_sunat').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#tipo_documento').rules('add', { required: true, messages: {  required: "Campo requerido" } });
  $('#estado_documentos_update').rules('add', { required: true, messages: {  required: "Campo requerido" } });

});