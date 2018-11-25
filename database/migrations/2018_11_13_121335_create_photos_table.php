<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('floraName');
            $table->dateTime('dateTimeDigitized');
            $table->string('filePath');
            $table->string('fileName');
            $table->string('thumbnailFileName');
            $table->string('family')->nullable();
            $table->string('genus')->nullable();
            $table->string('species')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photos');
    }
}
