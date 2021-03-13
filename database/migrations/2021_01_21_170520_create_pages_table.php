<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->name(string, 200)->nullable();
            $table->title_top(string, 200)->nullable();
            $table->title_main(string, 200)->nullable();
            $table->title_doc(string, 200)->nullable();
            $table->table_data(json)->nullable();
            $table->posibility(json)->nullable();
            $table->services(json)->nullable();
            $table->addition(json)->nullable();
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
        Schema::dropIfExists('pages');
    }
}
