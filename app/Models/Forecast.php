<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    use HasFactory;

    protected $table = 'forecasts';

    protected $fillable = [
        'user_id',
        'forecast_datetime',
        'predicted_energy_kwh',
    ];
}
