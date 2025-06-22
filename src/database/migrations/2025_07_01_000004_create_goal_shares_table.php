<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('GoalShares', function (Blueprint $table) {
            $table->id('share_id');
            $table->unsignedBigInteger('goal_id');
            $table->enum('share_type', ['private','public','friends','collaboration'])->default('private');
            $table->timestamp('shared_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('GoalShares');
    }
};
