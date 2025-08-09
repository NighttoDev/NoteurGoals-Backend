<?php

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
        Schema::create('messages', function (Blueprint $table) {
            // Khớp với collation của bảng Users
            $table->charset = 'utf8mb3';
            $table->collation = 'utf8mb3_general_ci';
            
            // Khóa chính
            $table->increments('id');

            // === THAY ĐỔI QUAN TRỌNG NHẤT ===
            // Thử tạo cột là INTEGER (có dấu) thay vì UNSIGNED INTEGER
            $table->integer('sender_id');
            $table->integer('receiver_id');

            $table->text('content');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')
                  ->references('user_id')->on('Users')
                  ->onDelete('cascade');
                  
            $table->foreign('receiver_id')
                  ->references('user_id')->on('Users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};