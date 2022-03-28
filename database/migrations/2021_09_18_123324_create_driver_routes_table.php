<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sequence');
            $table->date('ride_date');
            $table->time('ride_time');
            $table->unsignedInteger('seat_capacity');
            $table->softDeletes($column = 'deleted_at', $precision = 0);
            $table->timestamps();
            $table->unsignedBigInteger('driver_user_id');
            $table->foreign('driver_user_id')->references('id')->on('users');
            $table->unsignedBigInteger('route_status_id');
            $table->foreign('route_status_id')->references('id')->on('route_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('driver_routes');
    }
}
