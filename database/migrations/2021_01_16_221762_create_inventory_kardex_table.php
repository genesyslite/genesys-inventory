<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryKardexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_kardex', function (Blueprint $table) {
            $table->id();
            $table->date('date_of_issue');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('inventory_kardexable_id');
            $table->string('inventory_kardexable_type');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('quantity', 12, 4);
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_kardexes');
    }
}
