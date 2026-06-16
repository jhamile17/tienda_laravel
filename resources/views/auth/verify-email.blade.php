<!DOCTYPE html>
<html>
<head>
    <title>Verificar correo</title>
</head>
<body>

<h2>Verifica tu correo electrónico</h2>

<p>
Te enviamos un enlace de verificación a tu correo.
Debes verificar tu cuenta antes de ingresar a PROCAFES.
</p>

@if (session('message'))
    <p>{{ session('message') }}</p>
@endif

<form method="POST"
      action="{{ route('verification.send') }}">

    @csrf

    <button type="submit">
        Reenviar correo
    </button>

</form>

</body>
</html>