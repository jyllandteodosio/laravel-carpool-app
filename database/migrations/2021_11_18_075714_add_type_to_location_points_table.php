<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToLocationPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('location_points', function (Blueprint $table) {
            $table->string('type')->after('lat');
            $table->unsignedInteger('route_order')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('location_points', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('route_order');
        });
    }
}
