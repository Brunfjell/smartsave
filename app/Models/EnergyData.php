<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnergyData extends Model
{
    use HasFactory;

    protected $table = 'energy_data';

    protected $fillable = [
        'user_id',
        'device_id',
        'timestamp',
        'power_usage_watts',
        'voltage_volts',
        'current_amperes',
        'energy_consumption_kwh',
    ];
}
