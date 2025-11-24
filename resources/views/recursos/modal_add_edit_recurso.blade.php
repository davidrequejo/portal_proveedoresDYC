<!-- Modal Crear Recurso -->
<div class="modal fade" id="modalCrearRecurso" tabindex="-1" role="dialog" aria-labelledby="modalCrearRecursoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form action="{{ route('recursos.store') }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h5 class="modal-title text-white" id="modalCrearRecursoLabel">Agregar Nuevo Recurso</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="codigo">Código</label>
            <input type="text" class="form-control" name="codigo" required>
          </div>

          <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="3" required></textarea>
          </div>

          <div class="form-group">
            <label for="nivel">Nivel</label>
            <input type="number" class="form-control" name="nivel" min="1" required>
          </div>

          <div class="form-group">
            <label for="idtipoelemento">Tipo de Elemento</label>
            <select name="idtipoelemento" id="idtipoelemento" class="form-control select2" required>
            </select>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>