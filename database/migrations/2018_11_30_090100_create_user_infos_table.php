<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('openid');
            $table->string('nickName')->nullable();
            $table->string('avatarUrl')->nullable();
            $table->string('language')->nullable();
            $table->enum('gender', ['male','female'])->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_infos');
    }
}
