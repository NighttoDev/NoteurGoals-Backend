<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('GoalProgress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->unsignedBigInteger('goal_id');
            $table->float('progress_value');
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('GoalProgress');
    }
};
