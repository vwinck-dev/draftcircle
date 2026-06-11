<?php

declare(strict_types=1);

use App\Enums\BookFormat;
use App\Enums\LoanStatus;
use App\Enums\LoanType;
use App\Enums\SettingGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string(column: 'name');
            $table->string(column: 'slug')->unique();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string(column: 'title');
            $table->string(column: 'author');
            $table->string(column: 'publisher')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string(column: 'language', length: 10)->default('en');
            $table->string(column: 'isbn')->nullable()->unique();
            $table->text(column: 'description')->nullable();
            $table->string(column: 'cover_path')->nullable();
            $table->enum(column: 'format', allowed: array_column(BookFormat::cases(), 'value'));
            $table->unsignedInteger(column: 'total_copies')->default(0);
            $table->unsignedInteger(column: 'available_copies')->default(0);
            $table->unsignedInteger(column: 'loan_count')->default(0);
            $table->boolean(column: 'is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('book_categories', function (Blueprint $table): void {
            $table->foreignId(column: 'book_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'category_id')->constrained()->cascadeOnDelete();
            $table->primary(columns: ['book_id', 'category_id']);
        });

        Schema::create('book_ratings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId(column: 'book_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger(column: 'rating');
            $table->timestamps();
            $table->unique(columns: ['book_id', 'user_id']);
        });

        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId(column: 'user_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'book_id')->constrained()->cascadeOnDelete();
            $table->enum(column: 'type', allowed: array_column(LoanType::cases(), 'value'));
            $table->enum(column: 'status', allowed: array_column(LoanStatus::cases(), 'value'));
            $table->timestamp(column: 'reserved_at')->nullable();
            $table->timestamp(column: 'due_at')->nullable();
            $table->timestamp(column: 'returned_at')->nullable();
            $table->timestamps();
            $table->index(columns: ['status', 'due_at']);
        });

        Schema::create('fines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId(column: 'loan_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'user_id')->constrained()->cascadeOnDelete();
            $table->decimal(column: 'amount', total: 10, places: 2);
            $table->timestamp(column: 'paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('book_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId(column: 'book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger(column: 'page_number');
            $table->longText(column: 'content');
            $table->timestamps();
            $table->unique(columns: ['book_id', 'page_number']);
        });

        Schema::create('reading_progress', function (Blueprint $table): void {
            $table->id();
            $table->foreignId(column: 'user_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger(column: 'current_page')->default(1);
            $table->timestamps();
            $table->unique(columns: ['user_id', 'book_id']);
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string(column: 'key')->unique();
            $table->text(column: 'value')->nullable();
            $table->enum(column: 'group', allowed: array_column(SettingGroup::cases(), 'value'));
            $table->string(column: 'type')->default('string');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid(column: 'id')->primary();
            $table->string(column: 'type');
            $table->morphs(name: 'notifiable');
            $table->text(column: 'data');
            $table->timestamp(column: 'read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(table: 'notifications');
        Schema::dropIfExists(table: 'settings');
        Schema::dropIfExists(table: 'reading_progress');
        Schema::dropIfExists(table: 'book_pages');
        Schema::dropIfExists(table: 'fines');
        Schema::dropIfExists(table: 'loans');
        Schema::dropIfExists(table: 'book_ratings');
        Schema::dropIfExists(table: 'book_categories');
        Schema::dropIfExists(table: 'books');
        Schema::dropIfExists(table: 'categories');
    }
};
