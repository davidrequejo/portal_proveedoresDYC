<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación de Actualización</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial, Helvetica, sans-serif;font-size:14px;color:#333;">

<!-- CONTENEDOR GENERAL -->
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5;">
    <tr>
        <td align="center">

            <!-- CONTENEDOR BLANCO -->
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;margin:20px auto;border-radius:10px;overflow:hidden;">

                <!-- HEADER -->
                <tr>
                    <td style="background-color:#231f20;padding:25px 30px;color:#ffffff;" align="center">

                        <img 
                            src="https://optimiza360.pe/portal_proveedor/public/assets/images/brand-logos/logo-dc-inmobiliario-grupo.png"
                            alt="DC Grupo Inmobiliario"
                            width="160"
                            style="display:block;margin-bottom:15px;border:0;"
                        >

                        <h2 style="margin:0;font-size:22px;font-weight:bold; text-align: center !important;">
                            Actualización de Información @if($tipo == 'proveedor')del Proveedor @elseif($tipo == 'cliente') del Cliente @endif
                        </h2>

                        <p style="margin:8px 0 0;font-size:14px; text-align: center !important;">
                            Información actualizada en el sistema
                        </p>

                    </td>
                </tr>

                <!-- CUERPO -->
                <tr>
                    <td style="padding:30px;">

                        <p style="margin:0 0 15px;">Hola, equipo de Logística. </p>

                        <p style="margin:0 0 20px;line-height:1.5;">
                            @if($tipo == 'proveedor')
                                Se ha actualizado la información del proveedor en el sistema:
                                <p style="margin:0 0 20px;line-height:1.5;">
                                  <strong>Proveedor:  </strong> {{ $data->nombre_razonsocial }}
                                </p>
                            @elseif($tipo == 'cliente')
                                Se ha actualizado la información del cliente en el sistema:
                                <p style="margin:0 0 20px;line-height:1.5;">
                                  <strong>Cliente:  </strong> {{ $data->nombre_razonsocial }}
                                    </p>
                            @endif
                        </p>

                        <p style="margin:0 0 20px;line-height:1.5;">
                            Para más detalles, puedes acceder al portal correspondiente a través del siguiente enlace:
                        </p>

                        <!-- BOTÓN -->
                        <p style="margin:30px 0 0;text-align:center;">
                            <a href="{{ config('app.url') }}"
                                style=" background-color:#2563eb; color:#ffffff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:bold; display:inline-block; width: 80%;"
                            >
                                Clic Para Ingresar al Portal
                            </a>
                        </p>

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td style="background-color:#f0f0f0;padding:20px 30px;font-size:12px;color:#666;text-align:center;">
                        <p style="margin:0;">
                            Este correo es informativo, por favor no responder a este mensaje.
                        </p>
                        <p style="margin:8px 0 0;">
                            © {{ date('Y') }} DyC Grupo Inmobiliario – Todos los derechos reservados
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
