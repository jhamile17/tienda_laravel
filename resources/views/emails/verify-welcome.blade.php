<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu correo | PROCAFES</title>
</head>

<body style="margin:0; padding:0; background-color:#f6f3f0; font-family:Arial, Helvetica, sans-serif; color:#342727;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#f6f3f0; padding:28px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:540px;">

                    {{-- Cabecera con logo --}}
                    <tr>
                        <td style="background-color:#3b2a2a; border-radius:10px 10px 0 0; padding:16px 24px; text-align:center;">
                            <img
                                src="{{ $message->embed(public_path('images/logo.jpg')) }}"
                                alt="PROCAFES"
                                width="52"
                                style="display:block; width:52px; height:auto; margin:0 auto 8px; border:0;"
                            >

                            <p style="margin:0; color:#ffffff; font-size:16px; font-weight:bold; letter-spacing:0.4px;">
                                PROCAFES
                            </p>
                        </td>
                    </tr>

                    {{-- Contenido --}}
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #e5dddd; border-top:0; border-radius:0 0 10px 10px; padding:26px 24px;">

                            <h1 style="margin:0 0 12px; font-size:22px; line-height:1.3; color:#342727;">
                                ¡Bienvenido{{ filled($user->name) ? ', ' . $user->name : '' }}!
                            </h1>

                            <p style="margin:0 0 12px; font-size:15px; line-height:1.55; color:#665a5a;">
                                Gracias por crear tu cuenta en PROCAFES.
                            </p>

                            <p style="margin:0 0 14px; font-size:15px; line-height:1.55; color:#665a5a;">
                                Para activar tu cuenta y puedas acceder a tu perfil, compras y pagos, verifica el correo con el que te registraste:
                            </p>

                            <p style="margin:0 0 20px; padding:10px 12px; background-color:#f6f3f0; border-left:3px solid #f4c430; border-radius:5px; font-size:14px; font-weight:bold; line-height:1.45; color:#342727; word-break:break-word;">
                                {{ $user->email }}
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;">
                                <tr>
                                    <td style="background-color:#c9282d; border-radius:7px;">
                                        <a
                                            href="{{ $url }}"
                                            style="display:inline-block; padding:12px 18px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:bold; line-height:1;"
                                        >
                                            Verificar mi correo
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 10px; font-size:13px; line-height:1.5; color:#665a5a;">
                                Por seguridad, este enlace vence en 60 minutos.
                            </p>

                            <p style="margin:0; font-size:13px; line-height:1.5; color:#665a5a;">
                                Si no creaste una cuenta en PROCAFES, puedes ignorar este mensaje.
                            </p>

                        </td>
                    </tr>

                    {{-- Pie --}}
                    <tr>
                        <td align="center" style="padding:14px 8px 0;">
                            <p style="margin:0; font-size:11px; line-height:1.5; color:#8a7d7d;">
                                © {{ date('Y') }} PROCAFES. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>