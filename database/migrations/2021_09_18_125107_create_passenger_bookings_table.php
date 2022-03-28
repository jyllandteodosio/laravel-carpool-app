<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePassengerBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('passenger_bookings', function (Blueprint $table) {
            $table->id();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
            $table->timestamps();
            $table->unsignedBigInteger('passenger_user_id');
            $table->foreign('passenger_user_id')->references('id')->on('users');
            $table->unsignedBigInteger('driver_route_id');
            $table->foreign('driver_route_id')->references('id')->on('driver_routes');
            $table->unsignedBigInteger('pick_up_id');
            $table->foreign('pick_up_id')->references('id')->on('location_points');
            $table->unsignedBigInteger('drop_off_id');
            $table->foreign('drop_off_id')->references('id')->on('location_points');
            $table->unsignedBigInteger('booking_status_id');
            $table->foreign('booking_status_id')->references('id')->on('booking_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('passenger_bookings');
    }
}
