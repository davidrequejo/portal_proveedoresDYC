@php
  $appUrl = config('app.url');
  // IMPORTANTE: en correos usa URLs ABSOLUTAS para imágenes
  $bannerUrl = $bannerUrl ?? ($appUrl . '/img/mail/banner-proveedores.png'); // crea tu banner
  $logoUrl   = $logoUrl   ?? ($appUrl . '/img/mail/logo.png');              // tu logo
@endphp

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Credenciales</title>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f4f6;">
    <tr>
      <td align="center" style="padding:24px 12px;">

        <!-- Contenedor -->
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
               style="width:600px;max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08);">

          <!-- Banner -->
          <tr>
            <td>
              <img src="{{ $bannerUrl }}" width="600" alt="Banner"
                   style="display:block;width:100%;max-width:600px;height:auto;border:0;outline:none;text-decoration:none;">
            </td>
          </tr>

          <!-- Cuerpo -->
          <tr>
            <td style="padding:22px 26px 8px 26px;">
              <div style="font-family:Arial,Helvetica,sans-serif;font-size:22px;line-height:28px;color:#0b3aa4;font-weight:700;">
                Hola {{ $nombre ?? 'Proveedor' }},
              </div>

              <div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:20px;color:#111827;margin-top:10px;">
                Se creó tu acceso al <b>Portal de Proveedores</b>. Aquí tienes tus credenciales:
              </div>

              <!-- Caja credenciales -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:14px;">
                <tr>
                  <td style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:14px;">
                    <div style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:18px;color:#111827;">
                      <b>Usuario:</b> {{ $usuario ?? '-' }}<br>
                      <b>Clave:</b> {{ $clave ?? '-' }}
                    </div>
                    <div style="font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:18px;color:#6b7280;margin-top:10px;">
                      Por seguridad, te recomendamos cambiar tu clave al ingresar.
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Botón -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:16px;">
                <tr>
                  <td bgcolor="#0b3aa4" style="border-radius:10px;">
                    <a href="{{ $appUrl }}" target="_blank"
                       style="display:inline-block;padding:12px 18px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#ffffff;text-decoration:none;font-weight:700;">
                      Ingresar al portal
                    </a>
                  </td>
                </tr>
              </table>

              <!-- (Opcional) Bloque tipo encuesta 0-10 (como tu imagen) -->
              @if(!empty($encuestaUrl))
                <div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:20px;color:#111827;margin-top:22px;">
                  Considerando tu experiencia, ¿qué tan probable es que recomiendes el portal a un colega?
                </div>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:10px;">
                  <tr>
                    @for($i=0;$i<=10;$i++)
                      <td style="padding-right:6px;">
                        <a href="{{ $encuestaUrl }}?score={{ $i }}"
                           style="display:inline-block;width:30px;height:30px;line-height:30px;text-align:center;
                                  font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;
                                  color:#0b3aa4;text-decoration:none;border:1px solid #d1d5db;border-radius:999px;background:#ffffff;">
                          {{ $i }}
                        </a>
                      </td>
                    @endfor
                  </tr>
                </table>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:6px;">
                  <tr>
                    <td style="font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#6b7280;">Nada probable</td>
                    <td align="right" style="font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#6b7280;">Muy probable</td>
                  </tr>
                </table>
              @endif
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:18px 26px 22px 26px;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                     style="background:#0b3aa4;border-radius:12px;">
                <tr>
                  <td align="center" style="padding:18px;">
                    <img src="{{ $logoUrl }}" alt="Logo" height="34" style="display:block;border:0;outline:none;">
                  </td>
                </tr>
              </table>

              <div style="font-family:Arial,Helvetica,sans-serif;font-size:10.5px;line-height:16px;color:#6b7280;margin-top:14px;">
                Este correo fue enviado automáticamente. Si no solicitaste estas credenciales, comunícate con soporte.
              </div>
            </td>
          </tr>

        </table>
        <!-- /Contenedor -->

      </td>
    </tr>
  </table>
</body>
</html>
