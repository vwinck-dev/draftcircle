<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_comment_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_comment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('value');
            $table->timestamps();
            $table->unique(['book_comment_id', 'user_id']);
        });

        DB::table('book_comment_votes')->insertUsing(
            ['book_comment_id', 'user_id', 'value', 'created_at', 'updated_at'],
            DB::table('book_comments')->select([
                'id',
                'user_id',
                DB::raw('1'),
                DB::raw('CURRENT_TIMESTAMP'),
                DB::raw('CURRENT_TIMESTAMP'),
            ]),
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('book_comment_votes');
    }
};
