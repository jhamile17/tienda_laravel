<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\WelcomeVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasFactory, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'document_type',
        'document_number',
        'role',
        'address',
        // si en tu tabla existe, puedes agregar:
        // 'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // hash automático al asignar
    ];

    /* ================= Verificación de Email ================= */

    /**
     * Envía el correo de verificación usando tu plantilla personalizada.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new WelcomeVerifyEmail());
    }

    /* ================= Relaciones ================= */

    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /* ================= Helpers ================= */

    public function isAdmin(): bool
    {
        return (string)($this->role ?? '') === 'admin';
    }
}
