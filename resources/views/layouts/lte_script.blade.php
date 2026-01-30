<!-- jQuery -->
<script src="{{ asset('adminlte3/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('adminlte3/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('adminlte3/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- ChartJS -->
<script src="{{ asset('adminlte3/plugins/chart.js/Chart.min.js') }}"></script>
<!-- Sparkline -->
<script src="{{ asset('adminlte3/plugins/sparklines/sparkline.js') }}"></script>
<!-- JQVMap -->
<script src="{{ asset('adminlte3/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('adminlte3/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- Moment -->
<script src="{{ asset('adminlte3/plugins/moment/moment.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('adminlte3/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('adminlte3/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('adminlte3/plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('adminlte3/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminlte3/dist/js/adminlte.js') }}"></script>

<!-- jquery-validation -->
<script src="{{ asset('adminlte3/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/jquery-validation/additional-methods.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/jquery-validation/localization/messages_es_PE.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('adminlte3/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/select2/js/i18n/es.js') }}"></script>

<!-- Toastr -->
<script src="{{ asset('adminlte3/plugins/toastr/toastr.min.js') }}"></script>
<!-- sweetalert2 -->
<script src="{{ asset('adminlte3/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

<!-- pace-progress -->
<script src="{{ asset('adminlte3/plugins/pace-progress/pace.min.js') }}"></script>

<script>
  // Foco en el buscador de Select2
  $(document).on('select2:open', () => {  document.querySelector('.select2-search__field').focus(); });
</script>


<!-- DataTables  & Plugins -->
{{-- <script src="{{ asset('adminlte3/plugins/datatables2/jquery.dataTables.min.js') }}"></script>    
<script src="{{ asset('adminlte3/plugins/datatables2/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/datatables2/buttons.html5.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/datatables2/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/datatables2/jszip.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/datatables2/pdfmake.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/datatables2/vfs_fonts.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/datatables2/datetime.js') }}"></script> --}}
<!-- Responsive datatable -->
{{-- <script src="{{ asset('adminlte3/plugins/datatables2/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('adminlte3/plugins/datatables2/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script> --}}

<!-- Funciones generales -->
<script src="{{ asset('assets/js/funcion_crud.js') }}?version_erp=01.06"></script>
<script src="{{ asset('assets/js/funcion_general.js') }}?version_erp=01.03"></script>

<script>
    function alertaPerfilIncompleto() {

      let mensajes = [];

      @if(!auth()->user()->tiene_cuenta_bancaria)
        mensajes.push('• Registrar al menos una cuenta bancaria');
      @endif

      @if(auth()->user()->persona?->estado_completoxproveedor != 1)
        mensajes.push('• Completar datos del proveedor');
      @endif

      mensajes.push('• Cerrar sesión y volver a ingresar');

      Swal.fire("Para habilitar el módulo debe!", mensajes.map(m => '' + m).join('<br>') , "warning");

    }
</script>