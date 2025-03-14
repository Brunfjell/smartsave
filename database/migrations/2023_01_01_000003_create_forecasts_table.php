<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForecastsTable extends Migration
{
    public function up()
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->index()->name('fk_forecasts_user_id');
            $table->timestamp('forecast_datetime');
            $table->decimal('predicted_energy_kwh', 10, 2);
            $table->decimal('confidence_level', 5, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('forecasts');
    }
}
