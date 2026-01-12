
 
$("#editar_registro_proveedor").on("click", function (e) { $("#submit-form-editarproveedor").submit(); });   

 lista_select2('select2/bancos', '#idbanco');
 lista_select2('select2/obtener', '#distrito'); 

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

  $.getJSON(`/actualizardatosproveedor/${id}/ver_proveedorupdate`, function (e) {

    if (e.status === true) {
      console.log(e.data);

      
      $("#fecha_inicio_periodo").val(e.data.proveedor.fecha_inicio_periodo);
      $("#fecha_fin_periodo").val(e.data.proveedor.fecha_fin_periodo);

      $("#idpersona").val(e.data.proveedor.idpersona);

      $("#idtipo_persona").val(e.data.proveedor.idtipo_persona);
      $("#idtipoestandarproveedor").val(e.data.proveedor.idtipoestandarproveedor).trigger('change');
      $("#tipo_entidad_sunat").val(e.data.proveedor.tipo_entidad_sunat).trigger('change');
      $("#tipo_documento_input1").val(e.data.proveedor.tipo_documento).trigger('change');
      $("#numero_documento_input1").val(e.data.proveedor.numero_documento);
      $("#nombre_razonsocial_input1").val(e.data.proveedor.nombre_razonsocial);
      $("#nombre_persona_natural").val(e.data.proveedor.nombre_persona_natural);
      $("#apellido_paterno_per_natural").val(e.data.proveedor.apellido_paterno_per_natural);
      $("#apellido_materno_per_natural").val(e.data.proveedor.apellido_materno_per_natural);
      $("#celular").val(e.data.proveedor.celular);
      $("#direccion").val(e.data.proveedor.direccion);
      $("#distrito").val(e.data.proveedor.distrito).trigger('change');
      $("#provincia").val(e.data.proveedor.provincia);
      $("#departamento").val(e.data.proveedor.departamento);
      $("#email").val(e.data.proveedor.email);

      $('#tratamiento_pers_nat').val(e.data.proveedor.tratamiento_pers_natural).trigger('change');
      $('#sexo').val(e.data.proveedor.sexo).trigger('change');
      $("#numero_documento_input2").val(e.data.proveedor.ruc_persona_natural);
      $("#fecha_nacimiento").val(e.data.proveedor.fecha_nacimiento);

      let tipoDocumentoTexto = $('#tipo_documento_input1 option:selected').text();

      controlarCampos(tipoDocumentoTexto);

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


 function controlarCampos(tipoDocumentoTexto) {
  
  if (tipoDocumentoTexto=='DNI') {
    $('.div_razon_social').hide();
    $('#nombre_persona_natural').prop('required', true);
    $('#apellido_paterno_per_natural').prop('required', true);
    $('#apellido_materno_per_natural').prop('required', true);
    $('#sexo').prop('required', true);
    $('#tratamiento_pers_nat').prop('required', true);
    $('#numero_documento_input2').prop('required', true);

    $('.div_campos_pers_jud').hide();
    $('.div_campos_pers_nat').show();

    $('.class_col_dni_ruc_telefono').addClass('col-md-3 col-lg-3');
    $('.class_col_dni_ruc_email').addClass('col-md-4 col-lg-4');
    $('.class_col_dni_ruc_direccion').addClass('col-md-5 col-lg-5');

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

  let formData = new FormData($("#form-editar-proveedor")[0]);
  let id = $("#idpersonaUpdate").val();
  let url = '';

  if (id === '') {
    url = '/persona-cuenta-bancaria/crear';
  } else {
    url = `/actualizardatosproveedor/editarProveedor`;
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