<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AdminLTE 3 | Presupuestos</title>

    <link rel="icon" href="{{ asset('assets/images/brand-logos/ico-opt.png') }}" type="image/png">

    @include('layouts.lte_head')
    <link rel="stylesheet" href="{{ asset('assets/jstree-3.3.17/dist/themes/default/style.min.css') }}" />

    <style>
        #tabla-proyectos_filter {
            width: calc(100% - 10px) !important;
            display: flex !important;
            justify-content: space-between !important;
        }

        #tabla-proyectos_filter label {
            width: 100% !important;
        }

        #tabla-proyectos_filter label input {
            width: 100% !important;
        }

        /* Indicadores de orden simple (opcional) */
        th.sortable {
            cursor: pointer;
            position: relative;
        }

        th.sortable.asc::after {
            content: "▲";
            font-size: .7rem;
            position: absolute;
            right: .4rem;
        }

        th.sortable.desc::after {
            content: "▼";
            font-size: .7rem;
            position: absolute;
            right: .4rem;
        }
    </style>

</head>

<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        @include('layouts.lte_preloader')

        <!-- Navbar -->
        @include('layouts.lte_nav')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('layouts.lte_aside')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Recursos</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Recursos</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">

                        {{-- Arbol de Proyectos --}}
                        <div class="col-auto" style="width:300px; height: auto;">
                            <button type="button" class="btn btn-primary mb-3" data-toggle="modal"
                                data-target="#modalCrearRecurso">
                                <i class="fas fa-plus"></i> Nuevo Recurso
                            </button>

                            <div id="arbol-proyecto"
                                style="width:100%; height:auto; overflow-x:auto; overflow-y:auto; border:1px solid #ccc; padding:5px;">

                            </div>
                        </div>
                        <!-- ./col -->
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title nombre_nivel">Bordered Table</h3>
                                </div>
                                <!-- /.card-header -->

                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col">
                                            <input id="buscar" class="form-control form-control-sm"
                                                placeholder="Buscar proyecto...">
                                        </div>
                                        <div class="col-auto">
                                            <select id="perPage" class="form-select form-select-sm">
                                                <option value="5">5</option>
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>
                                    <table class="table table-bordered" id="tabla_recursos">
                                        <thead>
                                            <tr>
                                                <th data-sort="idprecurso" class="sortable">ID</th>
                                                <th data-sort="codigo" class="sortable">Código</th>
                                                <th data-sort="descripcion" class="sortable">Descripción</th>                                                
                                                <th data-sort="tipo_elemento" class="sortable">Tipo Elemento</th>                                                
                                                <th data-sort="nivel"class="sortable">Nivel</th>
                                                <th data-sort="creado" class="sortable">Creado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer clearfix">
                                    <ul class="pagination pagination-sm m-0 float-right" id="paginacion">

                                    </ul>
                                </div>



                            </div>
                        </div>
                    </div>
                    <!-- /.row -->

                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->



        @include('layouts.lte_footer')

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    @include('layouts.lte_script')
    @include('recursos.modal_add_edit_recurso')

    <script src="{{ asset('assets/jstree-3.3.17/dist/jstree.min.js') }}"></script>
    <script src="{{ asset('assets/js/recurso.js') }}"></script>



</body>

</html>
