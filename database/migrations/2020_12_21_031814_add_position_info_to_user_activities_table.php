<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionInfoToUserActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->string('ip_address')->default('');
            $table->string('country')->default('');
            $table->string('device')->default('');
            $table->string('platform')->default('');
            $table->string('browser')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropColumn('browser');
            $table->dropColumn('platform');
            $table->dropColumn('device');
            $table->dropColumn('country');
            $table->dropColumn('ip_address');
        });
    }
}
