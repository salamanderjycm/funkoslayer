<!DOCTYPE html>
<html>
<head>
    <style>
        body { background-color: #030712; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background-color: #111827; border: 1px solid #1f2937; border-radius: 12px; padding: 40px; text-align: center; }
        .logo { font-size: 28px; font-weight: 900; letter-spacing: 2px; font-style: italic; color: #ffffff; margin-bottom: 20px; }
        .logo span { color: #FF2A85; }
        .title { font-size: 20px; color: #e5e7eb; margin-bottom: 10px; }
        .code-box { background-color: #030712; border: 2px dashed #FF2A85; border-radius: 8px; padding: 20px; margin: 30px 0; font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #22d3ee; }
        .footer { font-size: 12px; color: #6b7280; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">FUNKO <span>SLAYER</span></div>
        <div class="title">Verificación de Cazador</div>
        <p style="color: #9ca3af; line-height: 1.6;">
            Has solicitado unirte a nuestras filas. Para completar tu registro y acceder al sistema, ingresa el siguiente código de seguridad en tu pantalla:
        </p>
        
        <div class="code-box">
            {{ $code }}
        </div>

        <p style="color: #9ca3af; font-size: 14px;">
            Si tú no solicitaste este código, puedes ignorar este correo con total seguridad.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} Funko Slayer. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>