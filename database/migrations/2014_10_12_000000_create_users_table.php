<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
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
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_token')->default('');
            $table->timestamp('token_period_at')->nullable();
            $table->string('phone_number')->default('');
            $table->string('ip_address')->default('');
            $table->string('country')->default('');
            $table->enum('blocked', [0, 1])->default(0);
            $table->enum('closed', [0, 1])->default(0);
            $table->enum('removed', [0, 1])->default(0);
            $table->rememberToken();
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
}
