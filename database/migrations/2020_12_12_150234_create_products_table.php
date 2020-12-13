<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('name_en')->default('');
            $table->string('name_hb')->default('');
            $table->text('description_en')->default('');
            $table->text('description_hb')->default('');
            $table->float('price')->default(0);
            $table->enum('product_type', ['meat', 'vegan', 'chili', 'alcohol'])->default('meat');
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
        Schema::dropIfExists('products');
    }
}
