<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('GoalCollaboration', function (Blueprint $table) {
            $table->id('collab_id');
            $table->unsignedBigInteger('goal_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['owner','member'])->default('member');
            $table->timestamp('joined_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('GoalCollaboration');
    }
};
