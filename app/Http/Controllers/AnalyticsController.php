<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnergyData;
use App\Models\EnvironmentalData;
use App\Models\Forecast;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpClient\HttpClient;

class AnalyticsController extends Controller
{
    public function index() {
        $kWhCharge = $this->fetchCurrentKwhCharge(); // Fetch current kWh charge

        // Fetch energy consumption data for the authenticated user
        $energyData = EnergyData::where('user_id', Auth::id())->get(['energy_consumption_kwh', 'power_usage_watts', 'voltage_volts', 'timestamp']);
        $predictedEnergy = Forecast::where('user_id', Auth::id())->pluck('predicted_energy_kwh');

        \Log::info('Energy Data:', $energyData->toArray()); // Debugging line

        $environmentData = EnvironmentalData::all();
        \Log::info('Environmental Data:', $environmentData->toArray()); // Debugging line
        $forecasts = Forecast::all();
        \Log::info('Forecasts:', $forecasts->toArray()); // Debugging line

        $energyConsumption = $energyData->pluck('energy_consumption_kwh'); 
        $timestamps = $energyData->pluck('timestamp'); 
        $wattsUsage = $energyData->pluck('power_usage_watts'); 
        $volts = $energyData->pluck('voltage_volts'); 
        $totalConsumption = $energyConsumption->sum(); // Calculate total monthly consumption
        $predictedMonthlyCharge = $totalConsumption * $kWhCharge; // Compute predicted monthly charge

return view('analytics', compact('energyData', 'environmentData', 'forecasts', 'energyConsumption', 'timestamps', 'predictedEnergy', 'kWhCharge', 'predictedMonthlyCharge', 'totalConsumption'));


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
