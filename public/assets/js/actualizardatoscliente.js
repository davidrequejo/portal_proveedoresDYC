const BASE_URL = document.querySelector('meta[name="app-url"]').content;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
 
$("#editar_registro_proveedor").on("click", function (e) { $("#submit-form-editarproveedor").submit(); });   

lista_select2(`${BASE_URL}/select2/bancos`, '#idbanco');
lista_select2(`${BASE_URL}/select2/obtener`, '#distrito'); 

$("#idbanco").select2({ theme: "bootstrap4", placeholder: "Selec. Banco", allowClear: true, });
$("#distrito").select2({ theme: "bootstrap4", placeholder: "Seleccionar Distrito", allowClear: true, });
$("#tratamiento_pers_nat").select2({ theme: "bootstrap4", placeholder: "Seleccionar Tratamiento", allowClear: true, }); 
$("#sexo").select2({ theme: "bootstrap4", placeholder: "Seleccionar Sexo", allowClear: true, }); 

$("#tratamiento_pers_nat").val('').trigger('change');
$("#sexo").val('').trigger('change');

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

function ver_editar_proveedor(){

  let id = $("#idpersonaUpdate").val();

  $.getJSON(`${BASE_URL}/actualizardatoscliente/${id}/ver_clienteupdate`, function (e) {

    if (e.status === true) {

      $("#idpersona").val(e.data.cliente.idpersona);

      $("#idtipo_persona").val(e.data.cliente.idtipo_persona);
      $("#idtipoestandarproveedor").val(e.data.cliente.idtipoestandarproveedor).trigger('change');
      $("#tipo_entidad_sunat").val(e.data.cliente.tipo_entidad_sunat).trigger('change');
      $("#tipo_documento_input1").val(e.data.cliente.tipo_documento).trigger('change');
      $("#numero_documento_input1").val(e.data.cliente.numero_documento);
      $("#nombre_razonsocial_input1").val(e.data.cliente.nombre_razonsocial);
      $("#nombre_persona_natural").val(e.data.cliente.nombre_persona_natural);
      $("#apellido_paterno_per_natural").val(e.data.cliente.apellido_paterno_per_natural);
      $("#apellido_materno_per_natural").val(e.data.cliente.apellido_materno_per_natural);
      $("#celular").val(e.data.cliente.celular);
      $("#direccion").val(e.data.cliente.direccion);
      $("#distrito").val(e.data.cliente.distrito).trigger('change');
      $("#provincia").val(e.data.cliente.provincia);
      $("#departamento").val(e.data.cliente.departamento);
      $("#email").val(e.data.cliente.email);

      $('#tratamiento_pers_nat').val(e.data.cliente.tratamiento_pers_natural).trigger('change');
      $('#sexo').val(e.data.cliente.sexo).trigger('change');
      $("#numero_documento_input2").val(e.data.cliente.ruc_persona_natural);
      $("#fecha_nacimiento").val(e.data.cliente.fecha_nacimiento);

      $("#nombre_apellidos_representante_legal").val(e.data.cliente.nombre_apellidos_representante_legal);
      $("#telefono_representante").val(e.data.cliente.numerotelefo_representante_legal);

      $("#nombre_apellidos_contacto_comercial").val(e.data.cliente.nombres_contacto_comercial);
      $("#telefono_contacto_comercial").val(e.data.cliente.telefono_contacto_comercial);
      $("#email_contacto_comercial").val(e.data.cliente.correo_contacto_comercial);
      $("#cargo_contacto_comercial").val(e.data.cliente.cargo_contacto_comercial);

      let tipoDocumentoTexto = $('#tipo_documento_input1 option:selected').text();
      let tipoentidadTexto = $('#tipo_entidad_sunat option:selected').text();

      controlarCampos(tipoDocumentoTexto, tipoentidadTexto);

      $(".vista_inicial").hide();
      $(".vista_datos").show();

    } else {
      toastr.error('Registro no encontrado');
    }

  }).fail(function (xhr) {
    ver_errores(xhr);
  });
}

ver_editar_proveedor();


function controlarCampos(tipoDocumentoTexto, tipoentidadTexto){

if ( tipoentidadTexto=='NATURAL') {
  $('.div_razon_social').show();
  $('#nombre_persona_natural').prop('required', true);
  $('#apellido_paterno_per_natural').prop('required', true);
  $('#apellido_materno_per_natural').prop('required', true);
  $('#sexo').prop('required', true);
  $('#tratamiento_pers_nat').prop('required', true);
  $('#numero_documento_input2').prop('required', true);

  $('.div_campos_pers_jud').hide();
  $('.div_campos_pers_nat').show();

  $('.class_col_dni_ruc_telefono').addClass('col-md-3 col-lg-3');
  $('.class_col_dni_ruc_email').addClass('col-md-5 col-lg-5');
  $('.class_col_dni_ruc_direccion').addClass('col-md-7 col-lg-7');

}else{
  $('.div_razon_social').show();
  $('.div_campos_pers_nat').hide();
  $('.div_campos_pers_jud').show();
  
  $('#nombre_razonsocial').prop('required', true);
  $('#numero_documento_input2').prop('required', false);
  
  $('#sexo').prop('required', false);
  $('#tratamiento_pers_nat').prop('required', false);
  $('#nombre_persona_natural').prop('required', false);
  $('#apellido_paterno_per_natural').prop('required', false);
  $('#apellido_materno_per_natural').prop('required', false);

  $('.class_col_dni_ruc_telefono').addClass('col-md-4 col-lg-4');
  $('.class_col_dni_ruc_email').addClass('col-md-5 col-lg-5');
  $('.class_col_dni_ruc_direccion').addClass('col-md-7 col-lg-7');
}

}


function editar_datosproveedor(e){

  $(".spiner_enviando_correo").show();
  $("#editar_registro_proveedor").hide();

  let formData = new FormData($("#form-editar-proveedor")[0]);
  let id = $("#idpersonaUpdate").val();
  let url = '';

  if (id === '') {
    url =`${BASE_URL}/persona-cuenta-bancaria/crear`;
  } else {
    url = `${BASE_URL}/actualizardatoscliente/editarcliente`;
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
        ver_editar_proveedor();
        Swal.fire("Correcto!", "Actualizado correctamente", "success");
        
        $(".spiner_enviando_correo").hide();
        $("#editar_registro_proveedor").show();
      } else {
        ver_errores(e);
      }
    },
    error: function (xhr) {
      ver_errores(xhr);
    }
  });
}


// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
// ═══════                                       J Q   F O R M   V A L I D A T I O N S                                                              ═══════
// ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
$(function () {    

  $('#sexo').on('change', function() { $(this).trigger('blur'); });
  $('#nombre_razonsocial').on('change', function() { $(this).trigger('blur'); });

  $("#form-editar-proveedor").validate({
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
      editar_datosproveedor(e);       
    },
  });

  $('#sexo').on('change', function() { $(this).trigger('blur'); });
  $('#nombre_razonsocial').on('change', function() { $(this).trigger('blur'); });

});


// Evitar que se abra el select al hacer clic
$('#tipo_documento_input1').on('mousedown', function (e) {  e.preventDefault();  this.blur();
  toastr.warning("Si no es el tipo Documento correcto Comunicate con el Administrador.");
});

$('#tipo_entidad_sunat').on('mousedown', function (e) {  e.preventDefault();  this.blur();
  toastr.warning("Si no es el tipo Entidad correcto Comunicate con el Administrador.");
});

$(document).ready(function() {
    // Cuando el valor del select cambia
    $('#distrito').change(function() {

      // Obtener el valor del atributo 'data-provincia' del option seleccionado
      var provincia = $(this).find('option:selected').data('provincia');
      var departamento = $(this).find('option:selected').data('departamento');

      $('#provincia').val(provincia);
      $('#departamento').val(departamento);
        
    });

});

