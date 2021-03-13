<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('type', 30);
            $table->string('name_organization', 200)->nullable();
            $table->string('name', 150)->nullable();
            $table->string('phone_work', 25)->nullable();
            $table->string('phone_mobile', 25)->nullable();
            $table->string('phone_more', 25)->nullable();
            $table->string('position', 25)->nullable();
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
        Schema::dropIfExists('organizations');
    }
}
