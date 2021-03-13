<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('phone', 20)->nulable();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 200);
            $table->string('name_organization', 250)->nullable();;
            $table->string('inn', 10)->unique()->nullable();;
            $table->string('ogrn', 20)->unique()->nullable();;
            $table->string('address', 180)->nullable();;
            $table->boolean('is_juristic');
            $table->string('api_token', 100)->nullable();
            $table->string('remember', 100)->nullable();
            $table->string('avatar_path', 250)->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
