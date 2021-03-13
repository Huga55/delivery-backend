<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('organization_id');
            $table->string('city', 30);
            $table->string('street', 40);
            $table->string('home', 10)->nullable();
            $table->string('corpus', 10)->nullable();
            $table->string('structure', 10)->nullable();
            $table->string('house_type', 30)->nullable();
            $table->string('apartment', 10)->nullable();
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
        Schema::dropIfExists('addresses');
    }
}
