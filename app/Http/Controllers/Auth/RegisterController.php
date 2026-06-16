<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'phone'    => ['nullable','string','max:20'],
            'address'  => ['nullable','string','max:255'],
            'document_type' => ['nullable','in:dni,ruc'],
            'document_number' => ['nullable','string','max:20'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'] ?? null,
            'address'  => $data['address'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'role' => 'customer',
        ]);

        // Dispara el correo de verificación
        event(new Registered($user));

        // Inicia sesión automáticamente
        Auth::login($user);

        // Enviar a la pantalla de verificar correo
        return redirect()->route('verification.notice');
    }
}