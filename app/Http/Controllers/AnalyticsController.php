<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnergyData;
use App\Models\EnvironmentalData;
use App\Models\Forecast;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Fetch energy consumption data for the authenticated user
        $energyData = EnergyData::where('user_id', Auth::id())->get(['energy_consumption_kwh', 'power_usage_watts', 'voltage_volts', 'timestamp']);

        \Log::info('Energy Data:', $energyData->toArray()); // Debugging line

        $environmentData = EnvironmentalData::all();
        \Log::info('Environmental Data:', $environmentData->toArray()); // Debugging line
        $forecasts = Forecast::all();
        \Log::info('Forecasts:', $forecasts->toArray()); // Debugging line

        $energyConsumption = $energyData->pluck('energy_consumption_kwh'); 
        $timestamps = $energyData->pluck('timestamp'); 
        $wattsUsage = $energyData->pluck('power_usage_watts'); 
        $volts = $energyData->pluck('voltage_volts'); 
        return view('analytics', compact('energyData', 'environmentData', 'forecasts', 'energyConsumption', 'timestamps'));
    }
}
