<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['token']);

            $table->dropColumn([
                'name',
                'email',
                'phone',
                'password',
                'token',
                'expires_at',
            ]);
        });
    }
};