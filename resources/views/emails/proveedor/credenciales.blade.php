<!doctype html>
<html>
<body style="font-family: Arial; background:#f5f5f5; padding:20px;">
  <div style="max-width:600px;margin:auto;background:#fff;border-radius:10px;padding:20px;">
    <h2>Hola, {{ $nombre }}</h2>
    <p>Se creó tu acceso al Portal de Proveedores.</p>

    <div style="background:#f0f0f0;padding:12px;border-radius:8px;">
      <p><b>Usuario:</b> {{ $usuario }}</p>
      <p><b>Clave:</b> {{ $clave }}</p>
    </div>

    <p style="margin-top:20px;">
      <a href="{{ config('app.url') }}" style="background:#2563eb;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none;">
        Ingresar al portal
      </a>
    </p>
  </div>
</body>
</html>