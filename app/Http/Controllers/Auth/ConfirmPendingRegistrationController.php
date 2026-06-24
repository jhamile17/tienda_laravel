<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfirmPendingRegistrationController extends Controller
{
    public function __invoke(string $token): RedirectResponse
    {
        $pending = PendingRegistration::where('token', $token)->firstOrFail();

        if ($pending->expires_at->isPast()) {
            $pending->delete();

            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'El enlace venció. Regístrate nuevamente para recibir otro.',
                ]);
        }

        if (User::where('email', $pending->email)->exists()) {
            $pending->delete();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Este correo ya tiene una cuenta. Inicia sesión.',
                ]);
        }

        $user = DB::transaction(function () use ($pending) {
    $pending = PendingRegistration::whereKey($pending->id)
        ->lockForUpdate()
        ->firstOrFail();

    if ($pending->expires_at->isPast()) {
        abort(410, 'El enlace de confirmación venció.');
    }

    if (User::where('email', $pending->email)->exists()) {
        $pending->delete();

        abort(409, 'Este correo ya tiene una cuenta.');
    }

            $user = User::create([
                'name' => $pending->name,
                'email' => $pending->email,
                'password' => $pending->password,
                'phone' => $pending->phone,
                'address' => null,
                'document_type' => null,
                'document_number' => null,
                'role' => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
            ]);

            $pending->delete();

            return $user;
        });
        Auth::login($user);

        return redirect()
            ->route('customer.dashboard')
            ->with('success', 'Correo confirmado. Tu cuenta fue creada correctamente.');
    }
}