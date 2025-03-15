<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Analytics') }}
        </h2>
        <p class="text-l text-gray-800 dark:text-gray-200 leading-tight mt-3">
            {{ __("Here are your analytics data") }}
        </p>
    </x-slot>

    <div class="mt-6 max-w-6xl mx-auto px-4">
        <h3 class="font-semibold text-2xl text-gray-800 dark:text-gray-200">Energy Data</h3>        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-white">
            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Consumption Data</h4>
                    <canvas id="energyChart" class="mt-2"></canvas>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            window.onload = function() {
                                const ctx = document.getElementById('energyChart');
                                if (!ctx) {
                                    console.error("Canvas element not found!");
                                    return;
                                }
                                const energyData = @json($energyData);
                                const energyConsumption = @json($energyConsumption);
                                const timestamps = @json($timestamps);
                                console.log("Energy Data:", energyData);
                                console.log("Timestamps:", timestamps);
                                new Chart(ctx.getContext('2d'), { 
                                    type: 'line', 
                                    data: {
                                        labels: timestamps.map(timestamp => {
                                            const date = new Date(timestamp);
                                            let hours = date.getHours();
                                            let ampm = hours >= 12 ? 'PM' : 'AM';
                                            hours = hours % 12 || 12;
                                            return `${hours} ${ampm}`;
                                        }),
                                        datasets: [
                                            {
                                                label: 'kWh',
                                                data: energyConsumption,
                                                borderColor: 'rgba(75, 192, 192, 1)',
                                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Watts',
                                                data: energyData.map(d => d.power_usage_watts), 
                                                borderColor: 'rgba(255, 99, 132, 1)',
                                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                                borderWidth: 1
                                            },
                                            {
                                                label: 'Voltage',
                                                data: energyData.map(d => d.voltage_volts), 
                                                borderColor: 'rgba(54, 162, 235, 1)',
                                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                                borderWidth: 1
                                            }
                                        ]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true
                                            }
                                        }
                                    }
                                });
                            };
                        });
                    </script>

            </div>

            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Predicted Energy kWh</h4>
                <canvas id="predictedEnergyChart" class="mt-2"></canvas>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                    const predictedEnergyCtx = document.getElementById('predictedEnergyChart');

                    if (!predictedEnergyCtx) {
                        console.error("Canvas element for predictedEnergyChart not found!");
                        return;
                    }

                    const predictedEnergyData = @json($predictedEnergy);
                    const timestamps = @json($timestamps);

                    console.log("Predicted Energy Data:", predictedEnergyData);
                    console.log("Timestamps Data:", timestamps);

                    if (!predictedEnergyData || predictedEnergyData.length === 0) {
                        console.warn("No predicted energy data available.");
                        return;
                    }

                    new Chart(predictedEnergyCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: timestamps.map(timestamp => {
                                const date = new Date(timestamp);
                                let hours = date.getHours();
                                let ampm = hours >= 12 ? 'PM' : 'AM';
                                hours = hours % 12 || 12;
                                return `${hours} ${ampm}`;
                            }),
                            datasets: [{
                                label: 'Predicted Energy kWh',
                                data: predictedEnergyData,
                                borderColor: 'rgba(255, 206, 86, 1)',
                                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: false
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                });
                </script>
            </div>
            
            <div class="rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Total Monthly Consumption: </h4>
                <p>{{ isset($totalConsumption) ? number_format($totalConsumption, 2) . ' kWh' : 'No data available' }}</p>
                <h4 class="font-semibold">Predicted Monthly Charge: </h4>
                <p>₱{{ isset($predictedMonthlyCharge) ? number_format($predictedMonthlyCharge, 2) : 'No data available' }}</p>
                <h4 class="font-semibold">Charge/kWh <a href="https://company.meralco.com.ph/news-and-advisories/" class="btn btn-primary">
                    <i class="fas fa-link"></i> <!-- Font Awesome link icon -->
                </a></h4> 
                <p>{{ isset($kWhCharge) && $kWhCharge ? '₱' . number_format($kWhCharge, 4) . '/kWh' : 'No data available' }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6 max-w-6xl mx-auto px-4">
        <h3 class="font-semibold text-2xl text-gray-800 dark:text-gray-200">Environment Data</h3>
        <p class="text-l text-gray-800 dark:text-gray-200 leading-tight mt-3">
            {{ __("Details about environmental conditions and metrics.") }}
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    
</x-app-layout>
