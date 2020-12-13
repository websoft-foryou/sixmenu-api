<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('page_name')->default('');
            $table->string('page_url')->default('');
            $table->string('user_action')->default('');
            $table->string('org_value')->default('');
            $table->string('new_value')->default('');
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
        Schema::dropIfExists('site_histories');
    }
}
