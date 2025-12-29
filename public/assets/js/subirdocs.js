//const { data } = require("autoprefixer");

$("#guardar_registro_docs_prov").on("click", function (e) { $("#submit-form-docs_prov").submit(); });   

lista_select2('/select2/lista_sin_docs_user', '#listar_docs_sin_subir'); 

$("#listar_docs_sin_subir").select2({ theme: "bootstrap4", placeholder: "Seleccionar tipo de documento", allowClear: true, });

ver_estados_docs_proveedor();
// ══════════════════════════════════════════════════════════════════════
// ══ F U N C I O N   A G R E G A R   I M A G E N   A P L I C A C I O N E S                                   ══
// ══════════════════════════════════════════════════════════════════════

$("#doc1_i").click(function() {  $('#doc1').trigger('click'); });
$("#doc1").change(function(e) {  addImageApplication(e,$("#doc1").attr("id")) });

// Eliminamos el DOC
function doc1_eliminar() { $("#doc1").val("");	$("#doc1_ver").html('<img src="/assets/svg/pdf.svg" alt="" width="50%" >');	$("#doc1_nombre").html(""); }

// ══════════════════════════════════════════════════════════════════════
// ══ F U N C I O N   S H O W   H I D E   E S C E N A R I O S                                              ══
// ══════════════
function show_hide_escenario(flag) {
  if (flag == 1) {            // Tabla principal
    $("#div-ver-documentos").show();
    $(".btn-agregar-proyecto").show();
    $(".btn-cancelar").hide();
    
  } else if (flag == 2) {     // Detalle proyecto
    $("#div-ver-documentos").hide();
    $(".btn-agregar-proyecto").hide();
    $(".btn-cancelar").show();
  } else if (flag == 3) {     //
  } else if (flag == 4) {
    
  }
}

    // capturamos el nombre del select y lo enviamos al input hidden
  $('#listar_docs_sin_subir').change(function() { var nombre = $(this).find('option:selected').text().trim(); $('#nombre_seleccion_tipo').val(nombre);  });


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

function limpiar_form_subir_doc(){
  
  //Mostramos los Materiales
  $("#iddocsproveedortipoestandar").val("");
  $("#nombre_seleccion_tipo").val("");
  $("#listar_docs_sin_subir").val("").trigger('change');
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
  $.getJSON(`/proyectos/${idpersona}/ver-editar`, function (e) {
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
    url_editar_crear =  `/subir_docs/guardar_doc_estandar_proveedor` ;    
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
         ver_estados_docs_proveedor()
          limpiar_form_subir_doc();
          Swal.fire("Correcto!", "Documento guardado correctamente", "success");  
          lista_select2('/select2/lista_sin_docs_user', '#listar_docs_sin_subir');      
          
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

function ver_estados_docs_proveedor() {
  show_hide_escenario(1);
  //$("#titulo-detalle-proyecto").html(`Documentos del Proveedor: <b class="text-info">${nombre_razonsocial}</b>`);
   $(".tbl_lista_documentos").html('<tr><td colspan="10" class="text-center text-muted">Ninguno</td></tr>');

   var cont =1;

  $.getJSON(`/subir_docs/listar_docs_tipos_est_xuser`,{}, function (e) {
    if (e.status == true) {
      $(".tbl_lista_documentos").empty('');
      e.data.forEach(r => {
        let estado_trash = r.estado_trash == 1 ? 'checked' : '';
        let estado_delete = r.estado_delete == 1 ? 'checked' : '';
      
        $(".tbl_lista_documentos").append(`
          <tr>
            <td class="py-1"> ${String(cont++).padStart(3, '0')} </td>
            <td class="py-1 text-nowrap" ><img src="/assets/images/default/pdf_icon.png" alt="Product 1" class="img-circle img-size-32 mr-2"> ${r.detalle ?? ''}</td>
            <td class="py-1 text-nowrap" ><span class="badge bg-warning">${r.estado_revision ?? ''}</span> </td>
            <td class="py-1 text-nowrap" ><a  class="text-muted" onclick="ver_documento_proveedor('${r.archivo ?? ''}','${r.detalle ?? ''}')"><i class="fas fa-search text-warning"></i></a></td>
            <td class="py-1 text-center" >
              <div class="btn-group btn-group-sm">
                <button class="btn btn-warning text-nowrap bnt-editar-proyecto" onclick="ver_editar_documento(${r.iddocsproveedortipoestandar}, '${r.nombreDocumento}')" data-toggle="tooltip" data-original-title="Editar"><i class="ti ti-edit"></i></button>
                <button class="btn btn-danger text-nowrap bn-ver-proyecto" onclick="eliminar_documento(${r.iddocsproveedortipoestandar}, '${r.nombreDocumento}')" data-toggle="tooltip" data-original-title="Eliminar"><i class="ti ti-trash"></i></button>
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

  $('.mostrar_documento_pdf').html(`  <object data="${ruta_documento}" type="application/pdf" width="100%" height="100%">
                            <p class="p-3 m-0">
                              Tu navegador no soporta visor PDF.
                              <a href="${ruta_documento}" target="_blank">Abrir PDF</a>
                            </p>
                          </object>`);

}

function ver_editar_documento(id, nombre_documento) { 

  console.log(nombre_documento);
  
  $(".nombre_doc_edit").text(`${nombre_documento}`);

  $("#modal-crear_documento").modal("show");

  $.getJSON(`/subir_docs/ver_doc_estandar/${id}`, function (e) {
    console.log(e.data);
    
    if (e.status == true) {

      $("#iddocsproveedortipoestandar").val(e.data.iddocsproveedortipoestandar);
      $("#nombre_seleccion_tipo").val(e.data.nombreDocumento);
      $("#listar_docs_sin_subir").val(e.data.iddetalletipoestandarproveedor).trigger('change');

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

  $('#listar_docs_sin_subir').on('change', function() { $(this).trigger('blur'); });

  $("#form-documentos-proveedor").validate({
    //ignore: '.select2-input, .select2-focusser',
    rules: {
      listar_docs_sin_subir:    { required: true, }, 
    },
    messages: {
      listar_docs_sin_subir:    { required: "Campo requerido.", },
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

  $('#listar_docs_sin_subir').rules('add', { required: true, messages: {  required: "Campo requerido" } });

});