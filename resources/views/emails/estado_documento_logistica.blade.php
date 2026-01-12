<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333333;
            font-size: 24px;
        }
        p {
            color: #555555;
            line-height: 1.6;
            font-size: 16px;
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        li {
            padding: 8px 0;
        }
        .highlight {
            color: #007BFF;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #888888;
            text-align: center;
        }
        .footer a {
            color: #007BFF;
            text-decoration: none;
        }
        .button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Actualización de Documento</h1>

    <p>Estimado/a <strong>{{ $proveedor->nombre_razonsocial }}</strong>,</p>

    <p>Le informamos que el estado de su documento ha sido actualizado. A continuación, los detalles:</p>

    <ul>
        <li><strong>Documento:</strong> <span class="highlight">{{ $documento }}</span></li>
        <li><strong>Estado Actual:</strong> <span class="highlight">{{ $estado }}</span></li>
        <li><strong>Observación:</strong> <span class="highlight">{{ $observacion }}</span></li>
    </ul>

    <p>
        Si tiene alguna observación o pregunta, no dude en responder directamente a este correo.
    </p>

    <p>Atentamente,</p>
    <p><strong>{{ $usuarioLogistica->name }}</strong><br>
    Área de Logística</p>

    <a href="mailto:{{ $proveedor->email }}" class="button">Responder a este correo</a>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Todos los derechos reservados.</p>
        <p><a href="#">Desuscribirse</a> | <a href="#">Ver en la web</a></p>
    </div>
</div>

</body>
</html>
