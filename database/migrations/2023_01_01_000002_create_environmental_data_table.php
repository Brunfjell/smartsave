<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnvironmentalDataTable extends Migration
{
    public function up()
    {
        Schema::create('environmental_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->index()->name('fk_environmental_data_user_id');
            $table->timestamp('timestamp');
            $table->decimal('temperature_celsius', 5, 2);
            $table->decimal('humidity_percent', 5, 2);
            $table->enum('source', ['sensor', 'api']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('environmental_data');
    }
}
