
(() => {
  const BASE_URL = document.querySelector('meta[name="app-url"]').content;
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;


  const state = {
    page: 1,
    per_page: 10,
    sort: 'codigo',
    dir: 'asc',
    q: ''
  };


   lista_select2(`${BASE_URL}/tipoestandar/select2docstipoestandar`, '#selectiddocumento_tipo_estandar');

   //$("#selectiddocumento_tipo_estandar").select2({ theme: "bootstrap4", placeholder: "Seleccionar", allowClear: true, });
   $("#selectiddocumento_tipo_estandar").select2();


    $("#guardar_registro_tipoestandar").on("click", function (e) { $("#submit-form-proveedor").submit(); });   
    tabla_principal_cargar();



  function show_hide_escenario(flag) {
    if (flag == 1) {            // Tabla principal
      $(".btn-agregar-tipo_estandar").show();
      $(".btn-cancelar").hide();
      
    } else if (flag == 2) {     // Detalle proyecto
      $(".btn-agregar-tipo_estandar").hide();
      $(".btn-cancelar").show();
    } else if (flag == 3) {     //
    } else if (flag == 4) {
      
    }
  }


  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
  // ═══════                                       S E C C I O N   T A B L A   P R O Y E C T O                                                        ═══════
  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════



  // Cargar datos
  function tabla_principal_cargar(){
    
    $.getJSON(`${BASE_URL}/tipoestandar/tabla_principal`, state, function(res){

      console.log(res.data);
      
      renderFilas(res.data);
      renderPaginacion(res.current_page, res.last_page);
      marcarOrden(state.sort, state.dir);
    }).fail(function (xhr) { ver_errores(xhr); });
  }

  // Render filas de la tabla
  function renderFilas(rows){
    const $tb = $("#tabla-tipo_estandar tbody").empty();
    if (!rows || rows.length === 0){
      $tb.append('<tr><td colspan="15" class="text-center text-muted">Sin resultados</td></tr>');
      return;
    }
    rows.forEach(r => {
      estado = r.estado_trash == '1'?' <span class="text-center badge badge-new">Activado</span>':'Deshabilitado';
      $tb.append(`
        <tr class="fila-proyecto" data-id="${r.idtipoestandarproveedor}">          
          <td class="py-1"> 
            <div class="btn-group btn-group-sm">
              <button class="btn text-nowrap btn-editar-tipoestand" data-id="${r.idtipoestandarproveedor}" data-toggle="tooltip" data-original-title="Editar"><i class="fas fa-pencil-alt color_icon_opt"></i></button>
              <button class="btn text-nowrap btn-eliminar-tipoestand"  data-id="${r.idtipoestandarproveedor}" data-name="${r.descripcion}"  data-toggle="tooltip" data-original-title="Ver"><i class="fas fa-trash color_icon_opt"></i></button>
            </div>
          </td>
          <td class="py-1 text-center" >${String(r.idtipoestandarproveedor).padStart(3, '0')}</td>
          <td class="py-1 text-nowrap" >${r.descripcion ?? ''}</td>
          <td class="py-1" >${r.nroDocumentos ?? ''}</td>
          <td class="py-1 text-nowrap">${ estado }</td>
          
        </tr>
      `);
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  }

  // Render paginación Bootstrap (ventana de 5 páginas)
  function renderPaginacion(actual, total){
    const $p = $("#paginacion_ts").empty();
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
    $("#tabla-tipo_estandar thead th.sortable").each(function(){ const $th = $(this);  const c = $th.data('sort');  $th.removeClass('asc desc'); if (c === col) $th.addClass(dir);  });
  }

  // Eventos: click en paginación
  $("#paginacion_ts").on("click", "a.page-link", function(e){  
    $("#tabla-tipo_estandar tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
    e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state.page = Math.max(1, page); tabla_principal_cargar(); } 
  });

  // Eventos: ordenar al hacer clic en header
  $("#tabla-tipo_estandar thead").on("click", "th.sortable", function(){
    $("#tabla-tipo_estandar tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Ordenando...</td></tr>');
    const col = $(this).data("sort"); if (state.sort === col) { state.dir = (state.dir === 'asc') ? 'desc' : 'asc'; } else { state.sort = col;  state.dir  = 'asc'; } state.page = 1;    
    tabla_principal_cargar();
  });

  // Búsqueda con debounce
  let t = null;
  $("#buscar_ts").on("input", function(){
    $("#tabla-tipo_estandar tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
    const val = $(this).val(); clearTimeout(t); t = setTimeout(function(){ state.q = val; state.page = 1; tabla_principal_cargar(); }, 300);
  });

  // Cambiar tamaño de página
  $("#perPage_ts").on("change", function(){
    $("#tabla-tipo_estandar tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
    state.per_page = parseInt($(this).val(), 10) || 20;  state.page = 1;
    tabla_principal_cargar();
  });



  $(".recargar-tabla_tipo_estandar").on("click", function(){
    toastr_info('<i class="ti ti-checks"></i> Actualizando...', 'Los datos se estan actualizado', 500);
    $("#tabla-tipo_estandar tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');    

    tabla_principal_cargar();
  });

  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
  // ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

  
    // EDITAR
  $(document).on('click', '.btn-editar-tipoestand', function () {
      const id = $(this).data('id');
      ver_editar_tipoestandar(id);
  });

  // ELIMINAR
  $(document).on('click', '.btn-eliminar-tipoestand', function () {
      const id = $(this).data('id');
      const nombre = $(this).data('name');
      eliminar_tipoestandar(id, nombre);
  });
  //LIMPIAR
  $(document).on('click', '.limpiar_form_tipoestandar', function () {
      limpiar_form_tipoestandar();
  });


  function limpiar_form_tipoestandar(){

    //Mostramos los Materiales
    $("#idtipoestandarproveedor").val('');
    $("#descripcion").val('');
    $("#nroDocumentos").val('');
    $("#selectiddocumento_tipo_estandar").val([]).trigger("change");


    // Limpiamos las validaciones
    $(".form-control").removeClass('is-valid');
    $(".form-control").removeClass('is-invalid');
    $(".error.invalid-feedback").remove();

  }

  function ver_editar_tipoestandar(idtipoestandarproveedor) {

    $("#cargando-1-formulario").hide();
    $("#cargando-2-formulario").show();
    limpiar_form_tipoestandar();
    $('#modal-agregar-tipoestandar').modal('show');

    $.getJSON(`${BASE_URL}/tipoestandar/${idtipoestandarproveedor}/ver-editar`, function (e) {

      if (e.status == true) {

        $("#idtipoestandarproveedor").val(e.data.idtipoestandarproveedor);
        $("#descripcion").val(e.data.descripcion);
        $("#nroDocumentos").val(e.data.nroDocumentos);
        //FALTA
         $("#selectiddocumento_tipo_estandar").val([]).trigger("change");

        // 🔥 AGREGAR SELECR MULTIPLE
        let valores = e.data.detalles.map(item => item.iddocumento_tipo_estandar);
        $("#selectiddocumento_tipo_estandar").val(valores).trigger("change");

        $("#cargando-1-formulario").show();
        $("#cargando-2-formulario").hide();

      } else {
        alert("No se encontró el tipo de estandar");
      }

    }).fail(function (xhr) {
      ver_errores(xhr);
    });
  }

  function guardar_y_editar_persona(e) {
    // e.preventDefault(); //No se activará la acción predeterminada del evento
    var formData = new FormData($("#form-agregar-tipoestandar")[0]);

    var id = $("#idtipoestandarproveedor").val();
    var url_editar_crear = '';
    if (id == '') {
      url_editar_crear =  `${BASE_URL}/tipoestandar/crear_tipoestandar` ;    
    } else {
      url_editar_crear = `${BASE_URL}/tipoestandar/editar_tipoestandar/${id}`;
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
            limpiar_form_tipoestandar();
            Swal.fire("Correcto!", "Tipo Estandar guardado correctamente", "success");          
            $("#modal-agregar-tipoestandar").modal("hide");           
          }else{
            ver_errores(e);				 
          }
        } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
        $("#guardar_registro_tipoestandar").html('Guardar Cambios').removeClass('disabled');
      },
      xhr: function () {
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener("progress", function (evt) {
          if (evt.lengthComputable) {
            var percentComplete = (evt.loaded / evt.total)*100; /*console.log(percentComplete + '%');*/
            $("#barra_progress_tipoestandar").css({"width": percentComplete+'%'}); $("#barra_progress_tipoestandar").text(percentComplete.toFixed(2)+" %");
          }
        }, false);
        return xhr;
      },
      beforeSend: function () {
        $("#guardar_registro_tipoestandar").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
        $("#barra_progress_tipoestandar").css({ width: "0%",  });
        $("#barra_progress_tipoestandar").text("0%");
      },
      complete: function () {
        $("#barra_progress_tipoestandar").css({ width: "0%", });
        $("#barra_progress_tipoestandar").text("0%");
      },
      error: function (jqXhr) { ver_errores(jqXhr); },
    });
  }

  function eliminar_tipoestandar(idtipoestandar, nombres) {

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
          url: `${BASE_URL}/tipoestandar/eliminar_tipoestandar/${idtipoestandar}`,
          type: "PUT",
          data: {
            _token: $('meta[name="csrf-token"]').attr('content') // necesario para PUT
          },
          success: function (e) {
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

    // validamos el formulario  

    $('#tipo_entidad_sunat').on('change', function() { $(this).trigger('blur'); });
    $('#tipo_documento').on('change', function() { $(this).trigger('blur'); });
    $('#selectiddocumento_tipo_estandar').on('change', function() { $(this).trigger('blur'); });
    

    $("#form-agregar-tipoestandar").validate({
      //ignore: '.select2-input, .select2-focusser',
      rules: {

        descripcion:    { required: true, },
        nroDocumentos:  { required: true, },    
        selectiddocumento_tipo_estandar :  { required: true, },    
      },
      messages: {
        descripcion:    { required: "Campo requerido.", },
        nroDocumentos:  { required: "Campo requerido.", },
        selectiddocumento_tipo_estandar:  { required: "Campo requerido.", },
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
    $('#selectiddocumento_tipo_estandar').rules('add', { required: true, messages: {  required: "Campo requerido" } });

  });


  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
  // ═══════                                       F I N   D E L   F I C H E R O                                                              ═══════
  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

  /*function actualizarNumeros() { $('#tabla_documentos tbody tr').each(function (index) { $(this).find('td:eq(0)').text(index + 1); });}

  // Agregar fila con límite
  $('#agregar_fila').on('click', function () {

      let limite = parseInt($('#nroDocumentos').val());  // cantidad máxima permitida
      let actuales = $('#tabla_documentos tbody tr').length;  // cantidad actual

      if (isNaN(limite) || limite <= 0) { toastr.warning('Primero ingresa un número válido en Nro Documentos.'); return; }

      if (actuales >= limite) { toastr.warning('Ha alcanzado el límite de documentos permitidos.'); return; }

      let nuevaFila = `
          <tr>
              <td class="text-center" style=" font-size: 12px; "></td>
              <td> <input type="text" class="form-control sin-borde" name="detalle[]" placeholder="Nombre del documento"> </td>
              <td class="text-center"> <button class="btn btn-danger btn-xs eliminar_fila"> <i class="ti ti-trash"></i> </button> </td>
          </tr>
      `;

      $('#tabla_documentos tbody').append(nuevaFila);
      actualizarNumeros();

      // 🚀 Toastr de éxito al agregar fila
      toastr.success('Fila agregada correctamente.');
  });

  // Eliminar fila
  $(document).on('click', '.eliminar_fila', function () { $(this).closest('tr').remove(); actualizarNumeros();});*/

})();
