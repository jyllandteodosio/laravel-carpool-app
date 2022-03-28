<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_points', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedInteger('points');
            $table->string('long');
            $table->string('lat');
            $table->softDeletes($column = 'deleted_at', $precision = 0);
            $table->timestamps();
            $table->unsignedBigInteger('driver_route_id');
            $table->foreign('driver_route_id')->references('id')->on('driver_routes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_points');
    }
}
