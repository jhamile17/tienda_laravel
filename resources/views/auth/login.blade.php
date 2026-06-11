@extends('layouts.app')
@section('title', 'Iniciar sesión | PROCAFES')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 h-screen">

  {{-- Imagen --}}
  <div class="hidden md:block bg-cover bg-center"
       style="background-image: url('{{ asset('images/cafe_register.jpg') }}')">
  </div>

  {{-- Formulario --}}
  <div class="flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">

      <h2 class="text-2xl font-bold mb-1">Iniciar sesión</h2>
      <p class="text-gray-500 mb-6">Usa tu correo y contraseña para continuar.</p>

      @if (session('status'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
          <label class="block font-medium mb-1">Correo electrónico</label>
          <input type="email" name="email"
                 value="{{ old('email') }}"
                 class="w-full border rounded-lg p-2 focus:ring focus:ring-yellow-300"
                 required autofocus>
        </div>

        <div class="mb-4">
          <label class="block font-medium mb-1">Contraseña</label>
          <input type="password" name="password"
                 class="w-full border rounded-lg p-2 focus:ring focus:ring-yellow-300"
                 required>
        </div>

        <div class="flex justify-between items-center mb-4 text-sm">
          <label class="flex items-center gap-2">
            <input type="checkbox" name="remember">
            Recordarme
          </label>

          <a href="#" class="text-gray-500 hover:underline">
            ¿Olvidaste tu contraseña?
          </a>
        </div>

        <button class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg">
          Ingresar
        </button>
      </form>

      <div class="text-center my-4 text-gray-400">o</div>

      <a href="{{ route('auth.google.redirect') }}"
         class="w-full border flex items-center justify-center gap-2 py-2 rounded-lg hover:bg-gray-50">
        <i class="bi bi-google"></i>
        Continuar con Google
      </a>

      <p class="text-center mt-4 text-sm">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" class="text-yellow-600 hover:underline">
          Regístrate
        </a>
      </p>

    </div>
  </div>

</div>
@endsection