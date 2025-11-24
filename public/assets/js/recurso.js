  let state ={ sort: 'codigo',};
  var codigo, descripcion = '';


//Función que se ejecuta al inicio js
function init() {

 getlistar_recursos_x_nivel();

 lista_select2('/select2/select2tipoelementos', '#idtipoelemento');

 $("#idtipoelemento").select2({ theme: "bootstrap4", placeholder: "Selecione Valorizacion", allowClear: true, });

}

function getlistar_recursos_x_nivel() {
  $.get('/recursos/listar_recursos_x_niveles', function (e) {
    try {
      if (e.status === true) {
        
        const recursos = e.data.data; // paginate o colección
        const treeData = buildJsTreeData(recursos);

        // reinicia el árbol si ya existe
        const $tree = $('#arbol-proyecto');
        
        const instancia = $tree.jstree(true);

        if (instancia && typeof instancia.destroy === 'function') { instancia.destroy(); }

        $tree.empty();

        // inicializa jsTree
        $tree
          // handler de selección (clic)
          .on('select_node.jstree', function (_evt, data) {
            const node = data.node;

            // Ignorar root u otros nodos sin payload
            if (!node || node.id === 'root') return;

            // Toma r desde node.data o desde node.original.data (según cómo lo alimentaste)
            const r = node.data || node.original?.data;

            console.log('payload r:', r);

            if (!r || typeof r.nivel === 'undefined') return; // sin data útil, no hacemos nada

            const nivel = Number(r.nivel);
            if (Number.isNaN(nivel)) return;

            // Si es nivel 4, pedir nivel 5 de ese prefijo
            if (nivel === 4) {

              state.page = 1;
              state.per_page = 10;
              state.dir = 'asc';
              state.nivel = '5';
              state.codigo = r.codigo;
              state.q = '';
              codigo = r.codigo; descripcion= r.descripcion;
              cargarNivelPorPrefijo();
            }
          })
          .jstree({
            core: { data: treeData, check_callback: true },
            plugins: ['contextmenu', 'wholerow'],
            contextmenu: {
              items: function (node) {
                const tree = $tree.jstree(true);
                return {
                  addChild: {
                    label: 'Agregar hijo',
                    action: function () {
                      $("#modalCrearRecurso").modal("show"); // abre tu modal
                    }
                  },
                  edit: { label: 'Editar', action: function(){ tree.edit(node); } },
                  duplicate: {
                    label: 'Duplicar',
                    action: function(){
                      const parent = tree.get_parent(node);
                      tree.copy_node(
                        node,
                        parent,
                        tree.get_node(parent).children.indexOf(node.id)+1,
                        function(copia){ tree.rename_node(copia, node.text + ' (copia)'); }
                      );
                    }
                  },
                  delete: {
                    label: 'Eliminar',
                    separator_before: true,
                    action: function(){
                      if (confirm('¿Eliminar este nodo?')) tree.delete_node(node);
                    }
                  }
                };
              }
            }
          });

      } else {
        ver_errores(e);
      }
    } catch (err) {
      console.error('Error:', err.message);
      toastr.error('Error construyendo el árbol');
    }
  }).fail(function(xhr){ ver_errores(xhr); });
}

// Construye los datos para el árbol de jstree
function buildJsTreeData(recursos) {
  // ordena por nivel y longitud de código para asegurar que el padre exista antes
  const items = [...recursos].sort((a,b) => (a.nivel - b.nivel) || (a.codigo.length - b.codigo.length));
  const byCodigo = {}; // mapa codigo -> nodo jsTree
  const root = { id: 'root', text: 'Todos los registros', state: { opened: true }, children: [] };

  items.forEach(r => {
    const node = {
      id: 'r_' + r.idrecurso,
      text: `${r.codigo} - ${r.descripcion}`,
      data: r,                  // ✅ aquí inyectas el payload
      children: [],
      li_attr: {                // opcional, útil para debug o selectores
        'data-nivel': r.nivel,
        'data-codigo': r.codigo
      }
    };
    byCodigo[r.codigo] = node;

    // buscar el padre: el de nivel-1 cuyo código sea prefijo del actual (tomar el más largo)
    let parentNode = null;
    if (r.nivel > 1) {
      const candidates = items.filter(p => p.nivel === r.nivel - 1 && r.codigo.startsWith(p.codigo));
      if (candidates.length) {
        candidates.sort((a,b) => b.codigo.length - a.codigo.length);
        const parent = byCodigo[candidates[0].codigo];
        if (parent) parentNode = parent;
      }
    }

    if (parentNode) parentNode.children.push(node);
    else root.children.push(node); // si no se encuentra padre, va directo a root
  });

  return [root];
}


function cargarNivelPorPrefijo() {

    $('.nombre_nivel').text(`${codigo} - ${descripcion}`);

    $.ajax({
        url: '/recursos/listar_recursos_ultimo_nivel',
        type: 'GET',
        dataType: 'json',
        data: state,
        success: function(e) {
          console.log(e.data);
          
              
          renderFilas(e.data);
          renderPaginacion(e.current_page, e.last_page);
          marcarOrden(state.sort, state.dir);

        },
        error: function(xhr) {
            console.error('Error nivel '+nivel+':', xhr.responseText);
            toastr.error('No se pudo cargar nivel '+nivel);
        }
    });
}


  // Render filas de la tabla
  function renderFilas(rows){
    const $tb = $("#tabla_recursos tbody").empty();
    if (!rows || rows.length === 0){
      $tb.append('<tr><td colspan="9" class="text-center text-muted">Sin resultados</td></tr>');
      return;
    }
    rows.forEach(r => {
      $tb.append(`
        <tr>
          <td class="py-1" >${r. idrecurso ?? ''}</td>
          <td class="py-1" >${r.codigo ?? ''}</td>
          <td class="py-1" >${r.descripcion ?? ''}</td>
          <td class="py-1" >${r.tipo_elemento ?? ''}</td>
          <td class="py-1" >${r.nivel ?? ''}</td>
          <td class="py-1" >${r.created_at ?? ''}</td>
          <td class="py-1"> 
            <div class="btn-group btn-group-sm">
              <a href="/recursos/${r. idrecurso}/edit" class="btn btn-primary">Editar</a>
              <a href="/recursos/${r. idrecurso}" class="btn btn-info">Ver</a>
            </div>
          </td>
        </tr>
      `);
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
    $("#tabla_recursos thead th.sortable").each(function(){ const $th = $(this);  const c = $th.data('sort');  $th.removeClass('asc desc'); if (c === col) $th.addClass(dir);  });
  }

  // Eventos: click en paginación
  $("#paginacion").on("click", "a.page-link", function(e){  
    $("#tabla_recursos tbody").html('<tr><td colspan="9" class="text-center text-muted">Buscando...</td></tr>');
    e.preventDefault();   const page = parseInt($(this).data("page"), 10); if (!isNaN(page)){ state.page = Math.max(1, page);  cargarNivelPorPrefijo(); } 
  });

  // Eventos: ordenar al hacer clic en header
  $("#tabla_recursos thead").on("click", "th.sortable", function(){
    $("#tabla_recursos tbody").html('<tr><td colspan="9" class="text-center text-muted">Ordenando...</td></tr>');
    const col = $(this).data("sort"); if (state.sort === col) { state.dir = (state.dir === 'asc') ? 'desc' : 'asc'; } else { state.sort = col;  state.dir  = 'asc'; } state.page = 1;    
     cargarNivelPorPrefijo();
  });

  // Búsqueda con debounce
  let t = null;
  $("#buscar").on("input", function(){
    $("#tabla_recursos tbody").html('<tr><td colspan="9" class="text-center text-muted">Buscando...</td></tr>');
    const val = $(this).val(); clearTimeout(t); t = setTimeout(function(){ state.q = val; state.page = 1;  cargarNivelPorPrefijo(); }, 300);
  });

  // Cambiar tamaño de página
  $("#perPage").on("change", function(){
    $("#tabla_recursos tbody").html('<tr><td colspan="9" class="text-center text-muted">Actualizando...</td></tr>');
    state.per_page = parseInt($(this).val(), 10) || 20;  state.page = 1;
     cargarNivelPorPrefijo();
  });


init();
