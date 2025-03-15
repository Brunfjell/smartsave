<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvironmentalData extends Model
{
    use HasFactory;

    protected $table = 'environmental_data';

    protected $fillable = [
        'user_id',
        'parameter',
        'value',
        'timestamp',
        'temperature_celsius',
        'humidity_percent',
    ];
}
