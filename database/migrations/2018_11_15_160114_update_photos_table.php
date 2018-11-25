<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        schema::table('photos', function (Blueprint $table) {
            $table->string('floraName')->nullable()->change();
            $table->dateTime('dateTimeDigitized')->nullable()->change();
        });    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    schema::table('photos', function (Blueprint $table) {
            $table->string('floraName')->nullable(false)->change();
            $table->dateTime('dateTimeDigitized')->nullable(false)->change();
        });    
    }
}
