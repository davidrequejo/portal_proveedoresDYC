/*const BASE_URL = document.querySelector('meta[name="app-url"]').content;*/
const CSRFF = document.querySelector('meta[name="csrf-token"]').content;

let idpersona_sincronizacion = '';
let razonsocial_sincronizacion = '';
let tipo_sincronizacion = '';

function sincronizacions10(idpersona, nombre_razonsocial, tipo) {

    console.log("Iniciando sincronización para:", { idpersona, nombre_razonsocial, tipo });
    idpersona_sincronizacion = idpersona;
    razonsocial_sincronizacion = nombre_razonsocial;
    tipo_sincronizacion = tipo;
    show_hide_escenario(3);
    $(".lista_cambios_proveedor").empty();
    $(".lista_cuentas_bancarias_proveedor").empty();

    $(".Nombre_inicial").html(
        `Sincronización con S10 Para <span class="text-principal hove-negrita"> : ${nombre_razonsocial} </span>`,
    );

    $.getJSON(
        `${BASE_URL}/logController/verdatosproveedor/${idpersona}`,
        function (e) {
            console.log(e);

            if (e.status == true) {
                if (
                    Array.isArray(e.data.ver_datos) &&
                    e.data.ver_datos.length === 0
                ) {
                    $(".btn_add_homologacion_show_view").show();
                } else {
                    $(".btn_add_homologacion_show_view").hide();
                }

                $(".tabla-list-homolog").empty();

                renderLogsProveedor(e.data.ver_datos);
                renderCuentasBancarias(e.data.cuentasbancarias);

                $('[data-toggle="tooltip"]').tooltip();
            } else {
                alert("No se encontró el proyecto");
            }
        },
    ).fail(function (xhr) {
        ver_errores(xhr);
    });
}

// Función para renderizar los logs del proveedor (ver_datos)
function renderLogsProveedor(logsArray) {
    $(".lista_cambios_proveedor").empty();

    if (!logsArray || logsArray.length === 0) {
        $(".lista_cambios_proveedor").html(
            '<p class="text-center">No hay registros de cambios.</p>',
        );
        return;
    }

    $.each(logsArray, function (index, log) {
        var observacion = {};
        try {
            observacion = JSON.parse(log.observacion);
        } catch (e) {
            observacion = { Error: "No se pudo parsear la observación" };
        }

        var collapseId = "collapse_" + log.idlogbd + "_" + index;

        // Color según estado_sincronizacions10
        var iconClass = "text-secondary";
        if (log.estado_sincronizacions10 == 0) {
            iconClass = "text-danger";
        } else if (log.estado_sincronizacions10 == 1) {
            iconClass = "text-success";
        }

        var fecha = log.created_at
            ? new Date(log.created_at).toLocaleString()
            : "Fecha no disponible";
        var estadoTexto =
            log.estado_sincronizacions10 == 0
                ? " (No sincronizado)"
                : log.estado_sincronizacions10 == 1
                  ? " (Sincronizado)"
                  : "";
        var titulo = log.accion_realizada + " - " + fecha;

        var detallesHtml = "";
        $.each(observacion, function (key, value) {
            detallesHtml +=
                '<p><i class="far fa-circle text-secondary"></i> ' +
                key +
                " : " +
                value +
                "</p>";
        });

        if (detallesHtml === "") {
            detallesHtml =
                '<p><i class="far fa-circle text-secondary"></i> No hay detalles adicionales</p>';
        }

        // Sin data-parent para permitir múltiples abiertos
        var cardHtml = `
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title w-100">
                        <a class="d-block w-100 text-principal text-bold" data-toggle="collapse" href="#${collapseId}">
                            <i class="fas fa-globe ${iconClass}"></i> ${titulo}
                        </a>
                    </h4>
                </div>
                <div id="${collapseId}" class="collapse ${index === 0 ? "show" : ""}">
                    <div class="card-body">
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item active">
                                ${detallesHtml}
                            </li>
                            <li class="nav-item active">                              
                               <a class="btn btn-sm btn-${log.estado_sincronizacions10 == 0 ? "warning" : "success"} mt-2">${estadoTexto} </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        `;

        $(".lista_cambios_proveedor").append(cardHtml);
    });
}

// Función para renderizar las cuentas bancarias (cuentasbancarias)
function renderCuentasBancarias(cuentasArray) {
    console.log(cuentasArray);
    
    $(".lista_cuentas_bancarias_proveedor").empty();

    if (!cuentasArray || cuentasArray.length === 0) {
        $(".lista_cuentas_bancarias_proveedor").html(
            '<p class="text-center">No hay registros de cuentas bancarias.</p>',
        );
        return;
    }

    $.each(cuentasArray, function (index, cuenta) {
        var observacion = {};
        try {
            observacion = JSON.parse(cuenta.observacion);
        } catch (e) {
            observacion = { Error: "No se pudo parsear la observación" };
        }

        // Extraer nombre del banco para el título
        var nombreBanco = "";
        if (observacion.Banco && observacion.Banco.nombre) {
            nombreBanco = " - " + observacion.Banco.nombre;
        } else if (observacion.Banco && typeof observacion.Banco === "string") {
            nombreBanco = " - " + observacion.Banco;
        }

        var collapseId = "collapse_cuenta_" + cuenta.idlogbd + "_" + index;

        var iconClass = "text-secondary";
        if (cuenta.estado_sincronizacions10 == 0) {
            iconClass = "text-danger";
        } else if (cuenta.estado_sincronizacions10 == 1) {
            iconClass = "text-success";
        }

        var fecha = cuenta.created_at
            ? new Date(cuenta.created_at).toLocaleString()
            : "Fecha no disponible";
        var estadoTexto =
            cuenta.estado_sincronizacions10 == 0
                ? " (No sincronizado)"
                : cuenta.estado_sincronizacions10 == 1
                  ? " (Sincronizado)"
                  : "";
        var titulo = cuenta.accion_realizada + nombreBanco + " - " + fecha;

        var detallesHtml = "";
        $.each(observacion, function (key, value) {
            if (typeof value === "object" && value !== null) {
                if (key === "Banco" && value.nombre) {
                    detallesHtml +=
                        '<p><i class="far fa-circle text-secondary"></i> ' +
                        key +
                        " : " +
                        value.nombre +
                        "</p>";
                } else {
                    detallesHtml +=
                        '<p><i class="far fa-circle text-secondary"></i> ' +
                        key +
                        " : " +
                        JSON.stringify(value) +
                        "</p>";
                }
            } else {
                detallesHtml +=
                    '<p><i class="far fa-circle text-secondary"></i> ' +
                    key +
                    " : " +
                    value +
                    "</p>";
            }
        });

        if (detallesHtml === "") {
            detallesHtml =
                '<p><i class="far fa-circle text-secondary"></i> No hay detalles adicionales</p>';
        }

        var cardHtml = `
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title w-100">
                        <a class="d-block w-100 text-principal text-bold" data-toggle="collapse" href="#${collapseId}">
                            <i class="fas fa-globe ${iconClass}"></i> ${titulo}
                        </a>
                    </h4>
                </div>
                <div id="${collapseId}" class="collapse ${index === 0 ? "show" : ""}">
                    <div class="card-body">
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item active">
                                ${detallesHtml}
                            </li>
                            <li class="nav-item active">                               
                               <a class="btn btn-sm btn-${cuenta.estado_sincronizacions10 == 0 ? "warning" : "success"} mt-2">${estadoTexto} </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        `;

        $(".lista_cuentas_bancarias_proveedor").append(cardHtml);
    });
}

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

function sincronizarproveedors10() {
    id_registrotabla = idpersona_sincronizacion;
    idlogbd = '';

    $(".btn_sincronizars10").html('Sincronizando...').addClass('disabled');
    if (!id_registrotabla) {
        alert("Error: ID de registro no válido");
        return;
    }

    const BASE_URL = window.location.origin;
    const urlxtipo = tipo_sincronizacion === 'proveedor' 
        ? `${BASE_URL}/proveedores/${id_registrotabla}/sincronizar-s10${idlogbd ? "/" + idlogbd : ""}` 
        : `${BASE_URL}/clientes/${id_registrotabla}/sincronizar-s10-cliente${idlogbd ? "/" + idlogbd : ""}`;

    console.log("URL de sincronización:", urlxtipo);
    //const url = `${BASE_URL}/proveedores/${id_registrotabla}/sincronizar-s10/${idlogbd || ""}`;

    $.ajax({
        url: urlxtipo,
        method: "GET",
        dataType: "json",
        success: function (e) {
            // Usamos 'success' en lugar de 'status'
            console.log(e);

            if (e.status == true) {
                Swal.fire("Exito!", e.message, "success");
                /* if (e.data && e.data.codigo_s10) {
                    console.log('Código S10 asignado:', e.data.codigo_s10);
                    // Aquí puedes actualizar la UI si lo deseas
                }*/
               $(".btn_sincronizars10").html('Sincronizar Con S10').removeClass('disabled');
                // Recargar los datos del proveedor para reflejar cambios
                sincronizacions10(idpersona_sincronizacion, razonsocial_sincronizacion, tipo_sincronizacion);
            } else {
                Swal.fire("Error!", e.message, "error");
            }
        },
        error: function (xhr) {
            let errorMsg = "Error en la sincronización.";

            // Intentar obtener el mensaje desde la respuesta JSON de Laravel (ApiResponse)
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            // Si no hay JSON, usar el texto de respuesta
            else if (xhr.responseText) {
                errorMsg = xhr.responseText;
            }
            // Último recurso: el estado del error
            else {
                errorMsg = xhr.statusText || "Ocurrió un error inesperado";
            }

            // Mostrar SweetAlert2
            Swal.fire({
                icon: "error",
                title: "Error",
                text: errorMsg,
                confirmButtonText: "Aceptar",
            });

            console.error(xhr);
        },
    });
}


function sincronizarcuentabancarias10() {

    id_registrotabla = idpersona_sincronizacion;
    idlogbd = '';

    $(".btn_sincronizarcbs10").html('Sincronizando...').addClass('disabled');
    if (!id_registrotabla) {
        alert("Error: ID de registro no válido");
        return;
    }

    const BASE_URL = window.location.origin;
    const url = `${BASE_URL}/proveedores/${id_registrotabla}/sincronizarcb-s10${idlogbd ? "/" + idlogbd : ""}`;

    $.ajax({
        url: url,
        method: "GET",
        dataType: "json",
        success: function (e) {
            // Usamos 'success' en lugar de 'status'
            console.log(e);

            if (e.status == true) {
                Swal.fire("Exito!", e.message, "success");
                /* if (e.data && e.data.codigo_s10) {
                    console.log('Código S10 asignado:', e.data.codigo_s10);
                    // Aquí puedes actualizar la UI si lo deseas
                }*/
               $(".btn_sincronizarcbs10").html('Sincronizar Con S10').removeClass('disabled');
                // Recargar los datos del proveedor para reflejar cambios
                sincronizacions10(idpersona_sincronizacion, razonsocial_sincronizacion, tipo_sincronizacion);
            } else {
                Swal.fire("Error!", e.message, "error");
            }
        },
        error: function (xhr) {
            let errorMsg = "Error en la sincronización.";

            // Intentar obtener el mensaje desde la respuesta JSON de Laravel (ApiResponse)
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            // Si no hay JSON, usar el texto de respuesta
            else if (xhr.responseText) {
                errorMsg = xhr.responseText;
            }
            // Último recurso: el estado del error
            else {
                errorMsg = xhr.statusText || "Ocurrió un error inesperado";
            }

            // Mostrar SweetAlert2
            Swal.fire({
                icon: "error",
                title: "Error",
                text: errorMsg,
                confirmButtonText: "Aceptar",
            });

            console.error(xhr);
        },
    });
}
