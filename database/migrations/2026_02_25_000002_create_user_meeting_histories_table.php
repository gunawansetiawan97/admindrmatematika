<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_meeting_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_activity_id')->constrained()->cascadeOnDelete();
            $table->date('meeting_date');
            $table->timestamps();

            $table->unique(['user_id', 'classroom_activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_meeting_histories');
    }
};
