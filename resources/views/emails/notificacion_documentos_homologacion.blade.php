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
                            Le informamos que los documentos
                            correspondientes a su proceso de <strong>homologación</strong>
                            han sido revisados.
                        </p>

                        <!-- TABLA DE DOCUMENTOS -->
                        <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;border:1px solid #dddddd;margin-top:20px;">
                            <thead>
                                <tr style="background-color:#f0f0f0;">
                                    <th align="left" style="border:1px solid #dddddd;">Documento</th>
                                    <th align="center" style="border:1px solid #dddddd;">Estado</th>
                                    <th align="left" style="border:1px solid #dddddd;">Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documentos as $doc)
                                    <tr>
                                        <td style="border:1px solid #dddddd;">
                                            {{ $doc->descripcion }}
                                        </td>
                                        <td align="center" style="border:1px solid #dddddd;">
                                            @if ($doc->estado_revision == 'Aprobado')
                                                ✅ <strong style="color:#2e7d32;">Aprobado</strong>
                                            @elseif ($doc->estado_revision == 'Observado')
                                                ⚠️ <strong style="color:#f9a825;">Observado</strong>
                                            @elseif ($doc->estado_revision == 'Actualizado')
                                                ⚠️ <strong style="color:#86888a;">Actualizado</strong>
                                            @else
                                                ❌ <strong style="color:#c62828;">Pendiente</strong>
                                            @endif
                                        </td>
                                        <td style="border:1px solid #dddddd;">
                                            {{ $doc->observacion ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- MENSAJE FINAL -->
                        <p style="margin:20px 0 0;line-height:1.5;">
                            En caso de que alguno de los documentos presente observaciones, le solicitamos regularizarlas a la brevedad posible a fin de continuar oportunamente con el proceso de homologación.

                            Si requiere orientación, soporte o tiene alguna consulta adicional, no dude en comunicarse con nosotros escribiendo al correo <strong>{{ $correoSoporte }}</strong>
                            , donde con gusto lo atenderemos.

                            Agradecemos su disposición y colaboración para el cumplimiento de los requisitos establecidos.
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
