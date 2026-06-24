<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ConfirmPendingRegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->middleware('guest')
        ->name('password.reset');
});

Route::get('confirm-pending-registration/{token}', ConfirmPendingRegistrationController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('pending-registration.confirm');
/*
Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::post('email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Enlace de verificación enviado por correo
|--------------------------------------------------------------------------
|
| No usa middleware auth para que funcione aunque el usuario abra el
| enlace desde otro navegador, perfil o dispositivo.
| La seguridad la mantienen la firma temporal, el hash y el throttle.
|
*/
/*
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');*/

