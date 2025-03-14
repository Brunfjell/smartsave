<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnergyDataTable extends Migration
{
    public function up()
    {
        Schema::create('energy_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->index()->onDelete('cascade')->name('fk_energy_data_user_id');
            $table->foreignId('device_id')->nullable()->constrained('devices');
            $table->timestamp('timestamp')->index();
            $table->decimal('power_usage_watts', 10, 2);
            $table->decimal('voltage_volts', 10, 2);
            $table->decimal('current_amperes', 10, 2);
            $table->decimal('energy_consumption_kwh', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('energy_data');
    }
}
