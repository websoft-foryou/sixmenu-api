<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('name_en');
            $table->string('name_hb');
            $table->text('description_en')->default('');
            $table->text('description_hb')->default('');
            $table->text('address_en')->default('');
            $table->text('address_hb')->default('');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('location')->default('');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            //$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurants');
    }
}
