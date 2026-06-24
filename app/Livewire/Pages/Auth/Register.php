<?php

namespace App\Livewire\Pages\Auth;

use App\Models\User;

use App\Models\PendingRegistration;
use App\Notifications\ConfirmPendingRegistrationEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
        ],
        [
            'email.unique' => 'Este correo ya está registrado. Inicia sesión o recupera tu contraseña.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]
        
        );

        $pending = PendingRegistration::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'phone' => $data['phone'] ?: null,
                'password' => Hash::make($data['password']),
                'token' => Str::random(64),
                'expires_at' => now()->addMinutes(60),
            ]
        );

        $pending->notify(new ConfirmPendingRegistrationEmail($pending));

        session()->flash(
            'status',
            'Te enviamos un enlace a tu correo. Tu cuenta se creará cuando confirmes el enlace.'
        );

        return redirect()->route('register');
        }

        public function render()
        {
            return view('livewire.pages.auth.register')
                ->layout('layouts.auth');
        }
        public function updatedEmail(): void
         {
        $this->validateOnly('email', [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
        ], [
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado. Inicia sesión o recupera tu contraseña.',
        ]);
    }
}