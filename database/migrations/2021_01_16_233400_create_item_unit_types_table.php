<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemUnitTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('item_id');
            $table->string('unit_type_id');
            $table->decimal('quantity_unit',12,4);
            $table->decimal('price1', 12, 4);
            $table->decimal('price2', 12, 4);
            $table->decimal('price3', 12, 4);
            $table->boolean('price_default')->default(2);
            $table->foreign('unit_type_id')->references('id')->on('cat_unit_types');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_unit_types');
    }
}
