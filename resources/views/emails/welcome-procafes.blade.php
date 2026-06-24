<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a PROCAFES</title>
</head>

<body style="margin:0; padding:0; background-color:#f7f5f2; font-family:Arial, Helvetica, sans-serif; color:#3b2a2a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; background-color:#f7f5f2; padding:24px 12px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:560px;">

                    {{-- Logo --}}
                    <tr>
                        <td align="center" style="padding:0 0 14px;">
                            <img
                                src="{{ $message->embed(public_path('images/logo.jpg')) }}"
                                alt="Logo PROCAFES"
                                width="64"
                                style="display:block; width:64px; max-width:64px; height:auto; border:0;"
                            >
                        </td>
                    </tr>

                    {{-- Tarjeta principal --}}
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #e7dddd; border-radius:10px; padding:26px 24px;">

                            <h1 style="margin:0 0 12px; font-size:22px; line-height:1.3; color:#3b2a2a;">
                                ¡Bienvenido, {{ $user->name }}!
                            </h1>

                            <p style="margin:0 0 12px; font-size:15px; line-height:1.55; color:#6f6262;">
                                Tu correo electrónico fue verificado correctamente.
                            </p>

                            <p style="margin:0 0 20px; font-size:15px; line-height:1.55; color:#6f6262;">
                                Ya puedes comprar, revisar tus pedidos y administrar tu cuenta en PROCAFES.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;">
                                <tr>
                                    <td style="background-color:#c9282d; border-radius:7px;">
                                        <a
                                            href="{{ route('customer.dashboard') }}"
                                            style="display:inline-block; padding:12px 18px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:bold; line-height:1;"
                                        >
                                            Ir a mi cuenta
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:14px; line-height:1.5; color:#6f6262;">
                                Gracias por ser parte de PROCAFES.
                            </p>

                        </td>
                    </tr>

                    {{-- Pie de página --}}
                    <tr>
                        <td align="center" style="padding:14px 8px 0;">
                            <p style="margin:0; font-size:12px; line-height:1.5; color:#8a7d7d;">
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