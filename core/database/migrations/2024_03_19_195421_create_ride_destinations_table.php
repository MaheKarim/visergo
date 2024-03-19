<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ride_destinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ride_id');
            $table->string('address');
            $table->double('destination_lat');
            $table->double('destination_long');
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
        Schema::dropIfExists('ride_destinations');
    }
};
