<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrchidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orchids', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('family');
            $table->string('genus');
            $table->string('species');
            $table->string('familyLatin');
            $table->string('genusLatin');
            $table->string('speciesLatin');
            $table->string('Document');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orchids');
    }
}
