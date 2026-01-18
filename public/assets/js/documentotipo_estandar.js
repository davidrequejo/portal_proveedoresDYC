    
(() => {

  const BASE_URL = document.querySelector('meta[name="app-url"]').content;
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;

  const state1 = {
    page: 1,
    per_page: 10,
    sort: 'codigo',
    dir: 'asc',
    q: ''
  };


  $("#guardar_registro_docs").on("click", function (e) { $("#submit-form-docs").submit(); });   
  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

    // Carga inicial
  tabla_principal_cargar_docstipo();

  // Cargar datos
  function tabla_principal_cargar_docstipo(){
    
    $.getJSON(`${BASE_URL}/documento-tipo-estandar/tabla_principal_conf_docs`, state1, function(res){

      console.log(res.data);
      
      renderFilas(res.data);
      renderPaginacion(res.current_page, res.last_page);
      marcarOrden(state1.sort, state1.dir);
    }).fail(function (xhr) { ver_errores(xhr); });
  }

  // Render filas de la tabla
  function renderFilas(rows){
    const $tbl = $("#tabladocumento-test tbody").empty();
    if (!rows || rows.length === 0){
      $tbl.append('<tr><td colspan="15" class="text-center text-muted">Sin resultados</td></tr>');
      return;
    }
    rows.forEach(r => {
      estado = r.estado_trash == '1'?' <span class="text-center badge badge-new">Activado</span>':'Deshabilitado';
      $tbl.append(`
        <tr class="fila_docs" data-id="${r.iddocumento_tipo_estandar}">          
          <td class="py-1"> 
            <div class="btn-group btn-group-sm">
              <button class="btn text-nowrap btn-editar-doc " data-id="${r.iddocumento_tipo_estandar}" data-toggle="tooltip" data-original-title="Editar"><i class="fas fa-pencil-alt color_icon_opt"></i></button>
              <button class="btn text-nowrap btn-eliminar-doc" data-id="${r.iddocumento_tipo_estandar}" data-name="${r.descripcion}" data-toggle="tooltip" data-original-title="Ver"><i class="fas fa-trash color_icon_opt"></i></button>
            </div>
          </td>
          <td class="py-1 text-center" >${String(r.iddocumento_tipo_estandar).padStart(3, '0')}</td>
          <td class="py-1 text-nowrap" >${r.descripcion ?? ''}</td>
          <td class="py-1 text-nowrap">${ estado }</td>
          
        </tr>
      `);
      $('[data-toggle="tooltip"]').tooltip(); 
    });
  }

  // Render paginación Bootstrap (ventana de 5 páginas)
  function renderPaginacion(actual, total){
    const $p = $("#paginacion_docs").empty();
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
    $("#tabladocumento-test thead th.sortable").each(function(){ const $th = $(this);  const c = $th.data('sort');  $th.removeClass('asc desc'); if (c === col) $th.addClass(dir);  });
  }

  // Eventos: click en paginación
  $("#paginacion_docs").on("click", "a.page-link", function(e){  
    $("#tabladocumento-test tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
    e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state1.page = Math.max(1, page); tabla_principal_cargar_docstipo(); } 
  });

  // Eventos: ordenar al hacer clic en header
  $("#tabladocumento-test thead").on("click", "th.sortable", function(){
    $("#tabladocumento-test tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Ordenando...</td></tr>');
    const col = $(this).data("sort"); if (state1.sort === col) { state1.dir = (state1.dir === 'asc') ? 'desc' : 'asc'; } else { state1.sort = col;  state1.dir  = 'asc'; } state1.page = 1;    
    tabla_principal_cargar_docstipo();
  });

  // Búsqueda con debounce
  let tb = null;
  $("#buscar_docs").on("input", function(){
    $("#tabladocumento-test tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Buscando...</td></tr>');
    const val = $(this).val(); clearTimeout(tb); tb = setTimeout(function(){ state1.q = val; state1.page = 1; tabla_principal_cargar_docstipo(); }, 300);
  });

  // Cambiar tamaño de página
  $("#perPage_docs").on("change", function(){
    $("#tabladocumento-test tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');
    state1.per_page = parseInt($(this).val(), 10) || 20;  state1.page = 1;
    tabla_principal_cargar_docstipo();
  });

  $(".recargar-tabla-documento").on("click", function(){
    toastr_info('<i class="ti ti-checks"></i> Actualizando...', 'Los datos se estan actualizado', 500);
    $("#tabladocumento-test tbody").html('<tr><td colspan="15" class="text-center text-muted"><i class="fas fa-sync fa-spin"></i> Actualizando...</td></tr>');    

    tabla_principal_cargar_docstipo();
  });

  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
  // ═══════                                       S E C C I O N   C R U D   P R O Y E C T O                                                          ═══════
  // ════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

    // EDITAR
  $(document).on('click', '.btn-editar-doc', function () {
      const id = $(this).data('id');
      ver_editar_docs(id);
  });

  // ELIMINAR
  $(document).on('click', '.btn-eliminar-doc', function () {
      const id = $(this).data('id');
      const nombre = $(this).data('name');
      eliminar_docs(id, nombre);
  });

  $(document).on('click', '.limpiar_form_docs', function () {
     limpiar_form_docs();
  });

  function limpiar_form_docs(){
    
    //Mostramos los Materiales
    $("#iddocumento_tipo_estandar").val('');
    $("#descripcion_docs").val('');

    // Limpiamos las validaciones
    $(".form-control").removeClass('is-valid');
    $(".form-control").removeClass('is-invalid');
    $(".error.invalid-feedback").remove();

    // 🔥 LIMPIAR TABLA DETALLES
    $("#tabla_documentos tbody").html("");
  }

  function ver_editar_docs(iddocumento_tipo_estandar) {

    console.log('ver_editar_docs ');
    

    $("#cargando-3-formulario").hide();
    $("#cargando-4-formulario").show();
    limpiar_form_docs();
    $("#modal-agregar-docs").modal('show');

    $.getJSON(`${BASE_URL}/documento-tipo-estandar/${iddocumento_tipo_estandar}/ver-editar_conf_docs`, function (e) {

      if (e.status == true) {

        $("#iddocumento_tipo_estandar").val(e.data.iddocumento_tipo_estandar);
        $("#descripcion_docs").val(e.data.descripcion);


        $("#cargando-3-formulario").show();
        $("#cargando-4-formulario").hide();

      } else {
        alert("No se encontró el tipo de estandar");
      }

    }).fail(function (xhr) {
      ver_errores(xhr);
    });
  }

  function guardar_y_editar_docs(e) {
    // e.preventDefault(); //No se activará la acción predeterminada del evento
    var formData = new FormData($("#form-agregar-docs")[0]);

    var id = $("#iddocumento_tipo_estandar").val();
    var url_editar_crear = '';
    if (id == '') {
      url_editar_crear =  `${BASE_URL}/documento-tipo-estandar/crear_conf_docs` ;    
    } else {
      url_editar_crear = `${BASE_URL}/documento-tipo-estandar/editar_conf_docs/${id}`;
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
            tabla_principal_cargar_docstipo();
            limpiar_form_docs();
            Swal.fire("Correcto!", "Documento guardado correctamente", "success");          
            $("#modal-agregar-docs").modal("hide");           
          }else{
            ver_errores(e);				 
          }
        } catch (err) { console.log('Error: ', err.message); toastr.error('<h5 class="font-size-16px">Error temporal!!</h5> puede intentalo mas tarde, o comuniquese con <i><a href="tel:+51921305769" >921-305-769</a></i> ─ <i><a href="tel:+51921487276" >921-487-276</a></i>'); } 
        $("#guardar_registro_docs").html('Guardar Cambios').removeClass('disabled');
      },
      xhr: function () {
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener("progress", function (evt) {
          if (evt.lengthComputable) {
            var percentComplete = (evt.loaded / evt.total)*100; /*console.log(percentComplete + '%');*/
            $("#barra_progress_docs").css({"width": percentComplete+'%'}); $("#barra_progress_docs").text(percentComplete.toFixed(2)+" %");
          }
        }, false);
        return xhr;
      },
      beforeSend: function () {
        $("#guardar_registro_docs").html('<i class="fas fa-spinner fa-pulse fa-lg"></i>').addClass('disabled');
        $("#barra_progress_docs").css({ width: "0%",  });
        $("#barra_progress_docs").text("0%");
      },
      complete: function () {
        $("#barra_progress_docs").css({ width: "0%", });
        $("#barra_progress_docs").text("0%");
      },
      error: function (jqXhr) { ver_errores(jqXhr); },
    });
  }

  function eliminar_docs(id, nombres) {

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
          url: `${BASE_URL}/documento-tipo-estandar/eliminar_conf_docs/${id}`,
          type: "PUT",
          data: {
            _token: $('meta[name="csrf-token"]').attr('content') // necesario para PUT
          },
          success: function (e) {
            console.log(e);

            if (e.status === true) {
              Swal.fire("Eliminado!", "El registro ha sido eliminado.", "success");
              tabla_principal_cargar_docstipo();
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

  let iddocs_select = null;
  let idpresup_select = null;

  // Ocultar menú al hacer clic en otro lugar
  $(document).on("click", () => {  
    $("#menu-contextual-proyecto").hide(); 
    $("#menu-contextual-add-presupuesto").hide(); 

  });

  // Mostrar menú contextual al hacer clic derecho en fila
  $(document).on("contextmenu", ".fila_docs", function (e) {
    e.preventDefault();
    
    $(".fila_docs").removeClass("selected");// Remover selección previa  
    $(this).addClass("selected");// Marcar esta fila como seleccionada
    iddocs_select = $(this).data("id");

    $("#menu-contextual-proyecto").css({ top: e.pageY + "px", left: e.pageX + "px", }).show();
  });

  // Opciones del menú contextual
  $("#opcion-p-editar").on("click", function (e) {
    e.preventDefault();
    if (iddocs_select) {
      ver_editar_docs(iddocs_select);
    }
  });

  $("#opcion-p-ver-detalle").on("click", function (e) {
    e.preventDefault();
    if (iddocs_select) {
      ver_detalle_proyecto(iddocs_select);
    }
  });

  $("#opcion-p-eliminar").on("click", function (e) {
    e.preventDefault();
    if (iddocs_select) {
      toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
    }
  });

  $("#opcion-p-enviar-terminado").on("click", function (e) {
    e.preventDefault();
    if (iddocs_select) {
      toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
    }
  });

  $("#opcion-p-enviar-papelera").on("click", function (e) {
    e.preventDefault();
    if (iddocs_select) {
      toastr_info('En desarrollo!!', 'Sea paciente, esta opcion esta disponible pronto.');
    }
  });

  $(document).on("contextmenu", ".fila_docs-presupuesto", function (e) {
    e.preventDefault();
    
    $(".fila_docs-presupuesto").removeClass("selected");// Remover selección previa  
    $(this).addClass("selected");// Marcar esta fila como seleccionada
    idpresup_select = $(this).data("idpresupuesto");

    if (idpresup_select == null || idpresup_select == '') {
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

    $("#form-agregar-docs").validate({
      //ignore: '.select2-input, .select2-focusser',
      rules: {

        descripcion_docs:    { required: true, }, 
      },
      messages: {
        descripcion_docs:    { required: "Campo requerido.", },
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
        guardar_y_editar_docs(e);       
      },
    });


  });

})();

