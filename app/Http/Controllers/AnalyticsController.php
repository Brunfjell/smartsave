<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EnergyData;
use App\Models\EnvironmentalData;
use App\Models\Forecast;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpClient\HttpClient;

class AnalyticsController extends Controller
{
    public function index() {

        $energyData = EnergyData::where('user_id', Auth::id())->get(['energy_consumption_kwh', 'power_usage_watts', 'voltage_volts', 'timestamp']);
        $predictedEnergy = Forecast::where('user_id', Auth::id())->pluck('predicted_energy_kwh');
        $temperatureData = EnvironmentalData::where('user_id', Auth::id())->get(['temperature_celsius','timestamp']);
        $humidityData = EnvironmentalData::where('user_id', Auth::id())->get(['humidity_percent','timestamp']);
        $environmentData = EnvironmentalData::all();
        $forecasts = Forecast::all();
        $energyConsumption = $energyData->pluck('energy_consumption_kwh'); 
        $timestamps = $energyData->pluck('timestamp'); 
        $wattsUsage = $energyData->pluck('power_usage_watts'); 
        $volts = $energyData->pluck('voltage_volts'); 
        $totalConsumption = $energyConsumption->sum(); 
        $kWhCharge = $this->fetchCurrentKwhCharge(); 
        $predictedMonthlyCharge = $totalConsumption * $kWhCharge;

        // Prepare data for Chart.js
        $temperatures = $temperatureData->pluck('temperature_celsius');
        $humidity = $humidityData->pluck('humidity_percent');
        $envTimestamps = $temperatureData->pluck('timestamp');

        return view('analytics', compact('energyData', 'environmentData', 'forecasts', 'energyConsumption', 'totalConsumption', 'timestamps', 'predictedEnergy', 'kWhCharge', 'predictedMonthlyCharge', 'temperatures', 'humidity', 'envTimestamps'));
    }

    public function getEnergyData(Request $request)
    {
        $range = $request->query('range', '24h'); // Default: Last 24 hours
        $userId = auth()->id(); // Get current logged-in user

        // Define date range conditions
        switch ($range) {
            case '7d':
                $startDate = now()->subDays(7);
                break;
            case '30d':
                $startDate = now()->subDays(30);
                break;
            case 'monthly':
                $startDate = now()->startOfMonth();
                break;
            default: // 24h
                $startDate = now()->subHours(24);
                break;
        }

        // Fetch energy data
        $energyData = DB::table('energy_data')
            ->where('user_id', $userId)
            ->where('timestamp', '>=', $startDate)
            ->orderBy('timestamp')
            ->get(['timestamp', 'power_usage_watts', 'voltage_volts', 'energy_consumption_kwh']);

        // Format timestamps and data
        $timestamps = $energyData->pluck('timestamp');
        $energyConsumption = $energyData->pluck('energy_consumption_kwh');

        return response()->json([
            'timestamps' => $timestamps,
            'energyConsumption' => $energyConsumption,
            'energyData' => $energyData
        ]);
    }

    public function fetchCurrentKwhCharge(): ?float {
        $baseUrl = 'https://company.meralco.com.ph/news-and-advisories/';
        $client = HttpClient::create();
    
        try {
            // Step 1: Fetch News & Advisories page
            $response = $client->request('GET', $baseUrl);
            $htmlContent = $response->getContent();
    
            // Step 2: Find the latest "higher-rates-[month]-[year]" link
            preg_match('/<a\s+href="(https:\/\/company\.meralco\.com\.ph\/news-and-advisories\/[^"]*higher-rates-[^"]*)"/i', $htmlContent, $matches);
    
            if (!isset($matches[1])) {
                \Log::warning('No Meralco rates page found. Check HTML structure.');
                return null;
            }
    
            $latestRatesUrl = $matches[1];
            \Log::info('Found latest Meralco rates page:', ['url' => $latestRatesUrl]);
    
            // Step 3: Fetch the rates page
            $response = $client->request('GET', $latestRatesUrl);
            $ratesHtml = $response->getContent();
    
            // Step 4: Extract the correct electricity rate
            preg_match('/overall rate for a typical household to\s*P?(\d+\.\d+)/i', $ratesHtml, $rateMatches);
    
            if (!empty($rateMatches[1])) {
                \Log::info('Extracted kWh Charge:', ['kWhCharge' => $rateMatches[1]]);
                return (float) $rateMatches[1];
            } else {
                \Log::warning('Failed to extract kWh charge. Check HTML.');
                return null;
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching Meralco kWh charge', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
