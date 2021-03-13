<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('id_of_dostavista')->nullable();
            $table->integer('user_id');
            $table->string('name_cargo', 100);
            $table->string('status', 50)->nullable();
            $table->string('address_from', 200);
            $table->string('address_to', 200);
            $table->string('type', 50);
            $table->string('track_number', 250)->nullable();
            $table->string('weight', 30)->nullable();
            $table->string('length', 30)->nullable();
            $table->string('width', 30)->nullable();
            $table->string('height', 30)->nullable();
            $table->string('size', 30)->nullable();
            $table->date('date_take');
            $table->date('date_delivery');
            $table->string('value_client', 20)->nullable();
            $table->string('pay_type', 30);
            $table->string('price', 20)->nullable();
            $table->json('time')->nullable();
            $table->json('errors')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
