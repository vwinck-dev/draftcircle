<?php

declare(strict_types=1);

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'name');
            $table->string(column: 'email')->unique();
            $table->string(column: 'registration_number')->nullable()->unique();
            $table->string(column: 'avatar')->nullable();
            $table->string(column: 'google_id')->nullable()->unique();
            $table->enum(column: 'role', allowed: array_column(UserRole::cases(), 'value'))->default(value: UserRole::Student->value);
            $table->boolean(column: 'email_notifications')->default(true);
            $table->string(column: 'theme')->default('system');
            $table->string(column: 'locale')->default('en');
            $table->timestamp(column: 'email_verified_at')->nullable();
            $table->string(column: 'password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string(column: 'email')->primary();
            $table->string(column: 'token');
            $table->timestamp(column: 'created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string(column: 'id')->primary();
            $table->foreignId(column: 'user_id')->nullable()->index();
            $table->string(column: 'ip_address', length: 45)->nullable();
            $table->text(column: 'user_agent')->nullable();
            $table->longText(column: 'payload');
            $table->integer(column: 'last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(table: 'users');
        Schema::dropIfExists(table: 'password_reset_tokens');
        Schema::dropIfExists(table: 'sessions');
    }
};
