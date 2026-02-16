<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación de Cuenta Bancaria</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial, Helvetica, sans-serif;font-size:14px;color:#333;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5;">
    <tr>
        <td align="center">

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

                        <h2 style="margin:0;font-size:22px;font-weight:bold;">
                            {{ $tipo == 'proveedor' ? 'Proveedor' : 'Cliente' }}
                            @switch($accion)
                                @case('agregar') agregó @break
                                @case('editar') actualizó @break
                                @case('desactivar') eliminó @break
                            @endswitch
                            cuenta bancaria
                        </h2>

                        <p style="margin:8px 0 0;font-size:14px;">
                            Información actualizada en el portal de Homologación
                        </p>
                    </td>
                </tr>

                <!-- CUERPO -->
                <tr>
                    <td style="padding:30px;">

                        <p>Hola, equipo.</p>

                        <p style="line-height:1.5;">
                            Se registró una actualización de cuenta bancaria con el siguiente detalle:
                        </p>

                        <p><strong>{{ $tipo == 'proveedor' ? 'Proveedor' : 'Cliente' }}:</strong> {{ $data->nombre_razonsocial }}</p>

                        <p>
                            <strong>Tipo de Cuenta:</strong>
                            @switch($cuenta->tipocuenta)
                                @case('C') Corriente @break
                                @case('A') Ahorros @break
                                @case('D') Detracción @break
                                @default No especificado
                            @endswitch
                        </p>

                        <p>
                            <strong>N° de Cuenta:</strong> {{ $cuenta->numero_cuenta }}
                        </p>

                        <p style="line-height:1.5;">
                            Para más detalles, puedes ingresar al portal correspondiente:
                        </p>

                        <p style="text-align:center;margin-top:25px;">
                            <a href="{{ config('app.url') }}"
                               style="background-color:#2563eb;color:#ffffff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block;width:80%;">
                                Ingresar al Portal
                            </a>
                        </p>

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td style="background-color:#f0f0f0;padding:20px 30px;font-size:12px;color:#666;text-align:center;">
                        <p style="margin:0;">
                            Este correo es informativo, por favor no responder.
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
