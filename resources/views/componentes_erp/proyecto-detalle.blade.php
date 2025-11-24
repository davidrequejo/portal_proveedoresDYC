<div class="row">
  <div class="col-md-3">

    <!-- About Me Box -->
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Datos del Proyecto</h3>
      </div>
      <!-- /.card-header -->
      <div class="card-body  ">
        <span><i class="fas fa-map-marker-alt mr-1"></i> Codigo: </span>
        <b><span class="span-proyecto-charge">{{ $proyecto->codigo ?? '—' }}</span></b>
        <hr class="my-1">
        <span ><i class="fas fa-book mr-1"></i> Descripcion</span> <br>
        <b><span class="span-proyecto-charge" >{{ $proyecto->descripcion ?? '—' }}</span></b> 
        <hr class="my-1">
        <span><i class="fas fa-map-marker-alt mr-1"></i> Direccion</span> <br>
        <b><span class="span-proyecto-charge" >{{ $proyecto->direccion ?? '—' }}</span></b>
        <hr class="my-1">
        <span ><i class="fas fa-map-marker-alt mr-1"></i> Ubicacion</span> <br>
        <b><span class="span-proyecto-charge" >{{ $proyecto->ubicacion ?? '—' }} </span></b>
        <hr class="my-1">
        <span><i class="ti ti-calendar-week mr-1"></i> Fecha: Inicio - Fin</span> <br>
        <b><span class="span-proyecto-charge" >{{ $fecha_inicio ?? '—' }} - {{ $fecha_fin ?? '—' }}</span></b>
        
        
      </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.col -->
  <div class="col-md-9">
    <div class="card">
        <div class="card-header p-2">
            <ul class="nav nav-pills">
                <li class="nav-item"><a class="nav-link active" href="#tab-presupuesto"  data-toggle="tab">Presupuesto</a></li>
                <li class="nav-item"><a class="nav-link" href="#tab-empresa-socio-negocio" data-toggle="tab">Empresa y Socio Negocio</a></li>
                <li class="nav-item"><a class="nav-link" href="#tab-settings" data-toggle="tab">Settings</a></li>
            </ul>
        </div><!-- /.card-header -->
        <div class="card-body">
            <div class="tab-content">
                <div class="active tab-pane" id="tab-presupuesto">

                    <div class="table-responsive">
                        <table class="table table-bordered b-radio-3px">
                            <thead class="b-radio-3px">
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th style="width: 40px">Codigo</th>
                                    <th>Presupuesto</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="fila-proyecto-presupuesto" data-idpresupuesto="5">
                                    <td class="py-1">1.</td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                </tr>
                                <tr class="fila-proyecto-presupuesto" data-idpresupuesto="">
                                    <td class="py-1">2.</td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                </tr>
                                <tr class="fila-proyecto-presupuesto" data-idpresupuesto="">
                                    <td class="py-1">3.</td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                </tr>
                                <tr class="fila-proyecto-presupuesto" data-idpresupuesto="">
                                    <td class="py-1">4.</td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                </tr>
                                <tr class="fila-proyecto-presupuesto" data-idpresupuesto="">
                                    <td class="py-1">5.</td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                    <td class="py-1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab-empresa-socio-negocio">
                    <div class="timeline-item">
                        <span class="time"><i class="far fa-clock"></i> 12:05</span>

                        <h3 class="timeline-header"><a href="#">Support Team</a> sent you an email</h3>

                        <div class="timeline-body">
                            Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                            weebly ning heekya handango imeem plugg dopplr jibjab, movity
                            jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                            quora plaxo ideeli hulu weebly balihoo...
                        </div>
                        <div class="timeline-footer">
                            <a href="#" class="btn btn-primary btn-sm">Read more</a>
                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                        </div>
                    </div>
                </div>
                <!-- /.tab-pane -->

                <div class="tab-pane" id="tab-settings">

                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div><!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.col -->
</div>
