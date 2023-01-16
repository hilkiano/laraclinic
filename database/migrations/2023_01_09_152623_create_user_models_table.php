<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->string('password');
            $table->string('name', 100);
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->dateTimeTz('email_verified_at')->nullable();
            $table->integer('otp')->nullable();
            $table->dateTimeTz('otp_timeout')->nullable();
            $table->integer('group_id');
            $table->string('remember_token')->nullable();
            $table->boolean('extended_login')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
