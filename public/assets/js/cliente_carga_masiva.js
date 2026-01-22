$(function () {

  let pasoActual = 1;

  function mostrarPaso(paso) {
    // Contenido
    $('.step-content').removeClass('active');
    $('.step-content[data-step="' + paso + '"]').addClass('active');

    // Indicador
    $('.step-indicator .step').removeClass('active');
    $('.step-indicator .step[data-step="' + paso + '"]').addClass('active');

    // Footer
    if (paso === 2) {
      $('#div-btn-importar-cliente').removeClass('d-none');
    } else {
      $('#div-btn-importar-cliente').addClass('d-none');
    }

    pasoActual = paso;
  }

  $('.btn-next').on('click', function () {
    mostrarPaso(2);
  });

  $('.btn-prev').on('click', function () {
    mostrarPaso(1);
  });


  



});
FilePond.registerPlugin(
  FilePondPluginImagePreview,
  FilePondPluginImageExifOrientation,
  FilePondPluginFileValidateSize,
  FilePondPluginImageEdit
);

FilePond.setOptions({
    labelIdle: 'Arrastra y suelta tus archivos o <span class="filepond--label-action">Examinar</span>',
    labelInvalidField: "El campo contiene archivos inválidos",
    labelFileWaitingForSize: "Esperando tamaño",
    labelFileSizeNotAvailable: "Tamaño no disponible",
    labelFileLoading: "Cargando",
    labelFileLoadError: "Error durante la carga",
    labelFileProcessing: "Subiendo",
    labelFileProcessingComplete: "Subida completa",
    labelFileProcessingAborted: "Subida cancelada",
    labelFileProcessingError: "Error durante la subida",
    labelFileProcessingRevertError: "Error durante la reversión",
    labelFileRemoveError: "Error durante la eliminación",
    labelTapToCancel: "Toca para cancelar",
    labelTapToRetry: "Tocar para reintentar",
    labelTapToUndo: "Tocar para deshacer",
    labelButtonRemoveItem: "Eliminar",
    labelButtonAbortItemLoad: "Cancelar",
    labelButtonRetryItemLoad: "Reintentar",
    labelButtonAbortItemProcessing: "Cancelar",
    labelButtonUndoItemProcessing: "Deshacer",
    labelButtonRetryItemProcessing: "Reintentar",
    labelButtonProcessItem: "Subir",
    labelMaxFileSizeExceeded: "El archivo es demasiado grande",
    labelMaxFileSize: "El tamaño máximo del archivo es {filesize}",
    labelMaxTotalFileSizeExceeded: "Tamaño total máximo excedido",
    labelMaxTotalFileSize: "El tamaño total máximo permitido es {filesize}",
    labelFileTypeNotAllowed: "Archivo de tipo inválido",
    fileValidateTypeLabelExpectedTypes: "Espera {allButLastType} o {lastType}",
    imageValidateSizeLabelFormatError: "Tipo de imagen no soportada",
    imageValidateSizeLabelImageSizeTooSmall: "La imagen es demasiado pequeña",
    imageValidateSizeLabelImageSizeTooBig: "La imagen es demasiado grande",
    imageValidateSizeLabelExpectedMinSize: "El tamaño mínimo es {minWidth} x {minHeight}",
    imageValidateSizeLabelExpectedMaxSize: "El tamaño máximo es {maxWidth} x {maxHeight}",
    imageValidateSizeLabelImageResolutionTooLow: "La resolución es demasiado baja",
    imageValidateSizeLabelImageResolutionTooHigh: "La resolución es demasiado alta",
    imageValidateSizeLabelExpectedMinResolution: "La resolución mínima es {minResolution}",
    imageValidateSizeLabelExpectedMaxResolution: "La resolución máxima es {maxResolution}",
});



// Crear instancia
const inputImportarExcel = document.querySelector('#input-plantilla-excel');
const pondImportarProyecto = FilePond.create(inputImportarExcel, {
  acceptedFileTypes: [
    'application/vnd.ms-excel',                                 // .xls
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // .xlsx
  ],
  maxFiles: 1,
});




$('#guardar_registro_importar_cliente').on('click', function () {

  let file = pondImportarProyecto.getFile();
  

  if (!file) {
    toastr.error("Debe seleccionar un archivo Excel");
    return;
  }

  let formData = new FormData();
  formData.append('file_excel_cliente_masivo', file.file);
  formData.append('_token', CSRF);

  $.ajax({
    url: `${BASE_URL}/cliente/importar_clientes_excel`,
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function (res) {
      if (res.status) {
        toastr.success(res.message);
        $('#modal-agregar-cliente-masivo').modal('hide');
        tabla_principal_cargar();
        $('.btn-bs-anterior').click();
        pondImportarProyecto.removeFile();
      } else {
        toastr.error(res.message);
      }
    },
    xhr: function () {
      var xhr = new window.XMLHttpRequest();
      xhr.upload.addEventListener("progress", function (evt) {
        if (evt.lengthComputable) {
          var percentComplete = (evt.loaded / evt.total)*100; /*console.log(percentComplete + '%');*/
          $("#barra_progress_importar_cliente").css({"width": percentComplete+'%'}); $("#barra_progress_importar_cliente").text(percentComplete.toFixed(2)+" %");
        }
      }, false);
      return xhr;
    },
    beforeSend: function () {
      $("#guardar_registro_importar_cliente").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
      $("#barra_progress_importar_cliente").css({ width: "0%",  }).text("0%");
      $("#barra_progress_importar_cliente_div").show();
    },
    complete: function () {
      $("#barra_progress_importar_cliente").css({ width: "0%", }).text("0%");
      $("#barra_progress_importar_cliente_div").hide();
    },    
    error: function (xhr) {
      console.error(xhr.responseText);
      toastr.error("Error inesperado al importar");
    }
  });

});
