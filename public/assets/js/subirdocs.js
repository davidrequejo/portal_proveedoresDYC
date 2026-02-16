//const { data } = require("autoprefixer");
const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

local_idpersona_facha_homologacion = null;

$("#guardar_registro_docs_prov").on("click", function (e) { $("#submit-form-docs_prov").submit(); });   


lista_periodos_homologacion();
// ══════════════════════════════════════════════════════════════════════
// ══ F U N C I O N   A G R E G A R   I M A G E N   A P L I C A C I O N E S                                   ══
// ══════════════════════════════════════════════════════════════════════

$("#doc1_i").click(function() {  $('#doc1').trigger('click'); });
$("#doc1").change(function(e) {  addImageApplication(e,$("#doc1").attr("id")) });

//$(".view_hidetipo_doc").hide();

// Eliminamos el DOC
function doc1_eliminar() { $("#doc1").val("");	$("#doc1_ver").html('<img src="/assets/svg/pdf.svg" alt="" width="50%" >');	$("#doc1_nombre").html(""); }

// ══════════════════════════════════════════════════════════════════════
// ══ F U N C I O N   S H O W   H I D E   E S C E N A R I O S                                              ══
// ══════════════
function show_hide_escenario(flag) {
  if (flag == 1) {            // Tabla principal
    $(".div_view_periodos_homologacion").show();
    $(".div_view_subir_documentos").hide();
    $(".btn-cancelar").hide();
    $(".btn_regresar_principal").hide();
    
  } else if (flag == 2) {     // Detalle proyecto
    $(".div_view_periodos_homologacion").hide();
    $(".div_view_subir_documentos").show();
    $(".btn-cancelar").show();
    $(".btn_regresar_principal").show();
  } else if (flag == 3) {     //
  } else if (flag == 4) {
    
  }
}

function lista_periodos_homologacion() {
  show_hide_escenario(1);
  //$("#titulo-detalle-proyecto").html(`Documentos del Proveedor: <b class="text-info">${nombre_razonsocial}</b>`);
   $(".tbl_lista_periodos_homologacion").html('<tr><td colspan="10" class="text-center text-muted">Ninguno</td></tr>');

   var cont =1;

  $.getJSON(`${BASE_URL}/subir_docs/periodo_homologacion`,{}, function (e) {
    console.log(e.data);
    
    if (e.status == true) {
      $(".tbl_lista_periodos_homologacion").empty('');
      e.data.forEach(r => {
        $(".tbl_lista_periodos_homologacion").append(`
          <tr>
            <td class="py-1"> ${String(cont++).padStart(3, '0')} </td>
            <td class="py-1 text-center" >
              <div class="btn-group btn-group-sm">
                <button class="btn btn-secondary text-nowrap " onclick="verdocumentos(${r.idpersona_facha_homologacion}, '${r.descripcion}')" data-toggle="tooltip" data-original-title="Ver Documentos">Ver Documentos <i class="far fa-eye"></i></button>
              </div>            
            </td>
            <td class="py-1" >${r.descripcion}</td>
            <td class="py-1 text-nowrap" >${r.fecha_inicio_proceso}</td>
            <td class="py-1 text-nowrap" >${r.fecha_fin_periodo_h ??'Por Asignar'}</td>
            <td class="py-1 text-nowrap" >${r.fecha_inicio_periodo_h??'Por Asignar'}</td>            
            <td class="py-1 text-center" ><span class="badge badge-new">${r.estado_homologacion ?? ''} </span> </td>

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

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function limpiar_form_subir_doc(){
  
  //Mostramos los Materiales
  $("#iddocsproveedortipoestandar").val("");
  doc1_eliminar();
  // Limpiamos las validaciones
  $(".form-control").removeClass('is-valid');
  $(".form-control").removeClass('is-invalid');
  $(".error.invalid-feedback").remove();
}

function ver_editar_proyecto(idpersona) {
  $("#cargando-1-formulario").hide();
  $("#cargando-2-formulario").show();
  limpiar_form_subir_doc();
  $('#modal-crear_documento').modal('show');
  $.getJSON(`${BASE_URL}/proyectos/${idpersona}/ver-editar`, function (e) {
    if (e.status == true) {
      $("#idpersona").val(e.data.idpersona);
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

function guardar_y_editar_docs_prov(e) {
  // e.preventDefault(); //No se activará la acción predeterminada del evento
  var formData = new FormData($("#form-documentos-proveedor")[0]);

  var id = $("#iddocsproveedortipoestandar").val();
  var url_editar_crear = '';
  if (id == '') {
    url_editar_crear =  `${BASE_URL}/subir_docs/guardar_doc_estandar_proveedor` ;    
  } else {
    url_editar_crear = `${BASE_URL}/subir_docs/editar_doc_estandar_proveedor/${id}`;
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
          ver_estados_docs_proveedor(local_idpersona_facha_homologacion); //refrescamos la tabla
          limpiar_form_subir_doc();
          Swal.fire("Correcto!", "Documento guardado correctamente", "success");    
          
          document.activeElement?.blur();
          $("#modal-crear_documento").modal("hide");           
        }else{
          ver_errores(e);				 
        }
      } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
      $("#guardar_registro_docs_prov").html('Guardar Cambios').removeClass('disabled');
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
      $("#guardar_registro_docs_prov").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
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

function verdocumentos(idpersona_facha_homologacion,descripcion){ 
  local_idpersona_facha_homologacion = idpersona_facha_homologacion;
  show_hide_escenario(2);
  $(".nombre_periodo_homologacion").html(`<strong>Perido Homologación : </strong>` +descripcion);
  $("#idpersona_facha_homologacion").val(idpersona_facha_homologacion);
  ver_estados_docs_proveedor(idpersona_facha_homologacion);
  
}

function ver_estados_docs_proveedor(idpersona_facha_homologacion) {

  show_hide_escenario(2);
  //$("#titulo-detalle-proyecto").html(`Documentos del Proveedor: <b class="text-info">${nombre_razonsocial}</b>`);
   $(".tbl_lista_documentos").html('<tr><td colspan="10" class="text-center text-muted"><i class="fas fa-sync-alt fa-spin"></i> Cargando ...</td></tr>');

   var cont =1;

  $.getJSON(`${BASE_URL}/subir_docs/listar_docs_tipos_est_xuser`,{idPeriodo: idpersona_facha_homologacion}, function (e) {
    if (e.status == true) {
      console.log(e);
      
      $(".tbl_lista_documentos").empty('');
      e.data.forEach(r => {

          let estadoHtml = ''; let donw_tipo_doc = '';


          const isPendiente = (r.estado_revision == 'Pendiente');

          switch (r.estado_revision) {
              case 'Actualizado': estadoHtml = `<span class="badge bg-warning text-dark">Actualizado</span>`; break;

              case 'Observado': estadoHtml = `<span class="badge bg-orange text-white" data-toggle="tooltip" data-original-title="${r.observacion ?? ''}" style="cursor: pointer;">Observado</span>`; break;

              case 'Aprobado': estadoHtml = `<span class="badge bg-success">Aprobado</span>`; break;

              case 'Rechazado': estadoHtml = `<span class="badge bg-dark">Rechazado</span>`; break;

              default: estadoHtml = `<span class="badge bg-danger">Pendiente.</span>`; 
          }
          donw_tipo_doc = r.tipo_documento == 'Modelo'?` <a type="button" class="btn btn-block btn-xs" href="${BASE_URL}/${r.archivo_modelo}" download="Modelo ${r.descripcion ?? ''}"> Descargar <i class="fas fa-cloud-download-alt fa-1x color_icon_opt"></i></a>`:'';



        $(".tbl_lista_documentos").append(`
          <tr>
            <td class="py-1 text-center"> ${String(cont++).padStart(3, '0')} </td>
            <td class="py-1" ><i class="fas fa-file-pdf fa-lg text-principal"></i> ${r.descripcion ?? ''}</td>
            <td class="py-1 text-center" >${estadoHtml} </td>
             <td class="py-1 text-center" >${donw_tipo_doc}</td>
            <td class="py-1 text-center" ><a  class="text-muted" onclick="ver_documento_proveedor('${r.archivo ?? ''}','${r.descripcion ?? ''}')"><i class="fas fa-search text-warning"></i></a></td>
            <td class="py-1 text-center" >
              <div class="btn-group btn-group-sm">
                <button class="btn text-nowrap bnt-editar-proyecto" onclick="ver_editar_documento(${r.iddocsproveedortipoestandar}, '${r.descripcion}')" data-toggle="tooltip" data-original-title="Editar"><i class="fas fa-pencil-alt color_icon_opt"></i></button>
              
              </div>            
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

function ver_documento_proveedor(ruta_documento, nombre_documento) {

  $(".nombre_doc_edit").text(`${nombre_documento}`);

  $(".nombre_documento_pdf").html(`Nombre : <b class="text-warning">${nombre_documento}</b>`);

  $('.mostrar_documento_pdf').html(``); 

  var doc_html = doc_view_extencion(ruta_documento, '', '', '100%', '100%' );

  $('.mostrar_documento_pdf').html(doc_html);

}

function ver_editar_documento(id, nombre_documento) { 

  $("#modal-crear_documento").modal("show");
  $(".nombre_tipo_documento").val(nombre_documento);

  $.getJSON(`${BASE_URL}/subir_docs/ver_doc_estandar/${id}`, function (e) {
    
    if (e.status == true) {

      $("#iddocsproveedortipoestandar").val(e.data.iddocsproveedortipoestandar);

      //validamoos DOC-1
      if (e.data.archivo && e.data.archivo.trim() !== "") {
        $("#doc_old_1").val(e.data.archivo);
        $("#doc1_nombre").html(`` + extrae_extencion(`${e.data.archivo}`) );
        $("#doc1_ver").html( doc_view_extencion(`${e.data.archivo}`, '', '', '100%', '210') );
      } else {
        $("#doc1_ver").html('<img src="/assets/svg/pdf.svg" alt="" width="50%">');
        $("#doc1_nombre").html('');
        $("#doc_old_1").val('');
      }


      $("#cargando-1-formulario").show();
      $("#cargando-2-formulario").hide();
    } else {
      alert("No se encontró el proyecto");
    }
  }).fail(function (xhr) { ver_errores(xhr); });
  
}

// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       J Q   F O R M   V A L I D A T I O N S                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
$(function () {    

  // validamos el formulario  

  $("#form-documentos-proveedor").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {
      //listar_docs_sin_subir:    { required: true, }, 
    },
    messages: {
      //listar_docs_sin_subir:    { required: "Campo requerido.", },
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
      guardar_y_editar_docs_prov(e);       
    },
  });


});