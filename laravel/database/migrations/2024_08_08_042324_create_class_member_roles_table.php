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
        Schema::create('class_member_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('class_role_id')->nullable();
            $table->unsignedBigInteger('class_room_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->on('users')->references('id');
            $table->foreign('class_role_id')->on('class_roles')->references('id');
            $table->foreign('class_room_id')->on('class_rooms')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_member_roles');
    }
};
