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
            $table->string(name, 200)->nullable();
            $table->string(title_top, 200)->nullable();
            $table->string(title_main, 200)->nullable();
            $table->string(title_doc, 200)->nullable();
            $table->json(table_data)->nullable();
            $table->json(posibility)->nullable();
            $table->json(services)->nullable();
            $table->json(addition)->nullable();
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
