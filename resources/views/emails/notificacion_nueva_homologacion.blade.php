<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación de Homologación</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial, Helvetica, sans-serif;font-size:14px;color:#333;">

<!-- CONTENEDOR GENERAL -->
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5;">
    <tr>
        <td align="center">

            <!-- CONTENEDOR BLANCO -->
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;margin:20px auto;">

                <!-- HEADER / BANNER -->
                <tr>
                    <td style="background-color:#231f20;padding:25px 30px;color:#ffffff;" align="center">

                        <img 
                            src="https://optimiza360.pe/portal_proveedor/public/assets/images/brand-logos/logo-dc-inmobiliario-grupo.png"
                            alt="DC Grupo Inmobiliario"
                            width="160"
                            style="display:block;margin-bottom:15px;border:0;"
                        >

                        <h2 style="margin:0;font-size:22px;font-weight:bold;">
                            ¡Tu información es importante para nosotros!
                        </h2>
                        <p style="margin:8px 0 0;font-size:14px;">
                            Proceso de Homologación de Proveedores
                        </p>

                    </td>
                </tr>


                <!-- CUERPO -->
                <tr>
                    <td style="padding:30px;">

                        <p style="margin:0 0 15px;">
                            Estimado(a)
                            <strong>{{ $proveedor->nombre_razonsocial ?? $proveedor->nombre }}</strong>,
                        </p>

                        <p style="margin:0 0 15px;line-height:1.5;">
                             Le informamos que se ha creado un nuevo proceso de homologación.  
                             Por favor, revise el proceso para proceder con el registro de la información necesaria.
                        </p>

                        <!-- MENSAJE FINAL -->
                        <p style="margin:20px 0 0;line-height:1.5;">
                            Si requiere orientación, soporte o tiene alguna consulta adicional, no dude en comunicarse con nosotros escribiendo al correo <strong>{{ $correoSoporte }}</strong>
                            , donde con gusto lo atenderemos.

                            Agradecemos su disposición y colaboración para el cumplimiento de los requisitos establecidos.
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

                        <!-- FIRMA -->
                        <p style="margin:25px 0 0;">
                            Atentamente,<br>
                            <strong>{{ $nombreSoporte }}</strong>
                            
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
                            © {{ date('Y') }} DC Grupo Inmobiliario – Todos los derechos reservados
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
