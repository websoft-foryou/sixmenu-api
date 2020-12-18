<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDayInfoToRestaurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('mon_from')->nullable();
            $table->string('mon_to')->nullable();
            $table->string('tue_from')->nullable();
            $table->string('tue_to')->nullable();
            $table->string('wed_from')->nullable();
            $table->string('wed_to')->nullable();
            $table->string('thu_from')->nullable();
            $table->string('thu_to')->nullable();
            $table->string('fri_from')->nullable();
            $table->string('fri_to')->nullable();
            $table->string('sat_from')->nullable();
            $table->string('sat_to')->nullable();
            $table->string('sun_from')->nullable();
            $table->string('sun_to')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('sun_to');
            $table->dropColumn('sun_from');
            $table->dropColumn('sat_to');
            $table->dropColumn('sat_from');
            $table->dropColumn('fri_to');
            $table->dropColumn('fri_from');
            $table->dropColumn('thu_to');
            $table->dropColumn('thu_from');
            $table->dropColumn('wed_to');
            $table->dropColumn('wed_from');
            $table->dropColumn('tue_to');
            $table->dropColumn('tue_from');
            $table->dropColumn('mon_to');
            $table->dropColumn('mon_from');
        });
    }
}
