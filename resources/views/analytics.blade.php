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

            <div class="rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Predicted Monthly Charge: </h4>
                <p>₱{{ isset($predictedMonthlyCharge) ? number_format($predictedMonthlyCharge, 2) : 'No data available' }}</p>
                <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-700">
                <h4 class="font-semibold">Total Monthly Consumption: </h4>
                <p>{{ isset($totalConsumption) ? number_format($totalConsumption, 2) . ' kWh' : 'No data available' }}</p>
                <hr class="h-px my-2 bg-gray-200 border-0 dark:bg-gray-700">
                <h4 class="font-semibold">Charge/kWh <a href="https://company.meralco.com.ph/news-and-advisories/" class="btn btn-primary">
                    <i class="fas fa-link"></i> <!-- Font Awesome link icon -->
                </a></h4> 
                <p>{{ isset($kWhCharge) && $kWhCharge ? '₱' . number_format($kWhCharge, 4) . '/kWh' : 'No data available' }}</p>
            </div>

            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <div class="mb-3 flex">
                    <h4 class="font-semibold flex-1">Consumption Data</h4>
                    <select id="timeRange" class="p-1 bg-gray-800 text-white text-xs rounded-md focus:ring-2 focus:ring-blue-500 flex-1">
                        <option value="24h" selected>Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <canvas id="energyChart" class="mt-2"></canvas>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const canvas = document.getElementById('energyChart');
                        if (!canvas) {
                            console.error("Canvas element not found!");
                            return;
                        }

                        const ctx = canvas.getContext('2d');
                        let energyChart;

                        function fetchData(timeRange) {
                            fetch(`/analytics/energy-data?range=${timeRange}`)
                                .then(response => response.json())
                                .then(data => {
                                    console.log("Fetched Data:", data); // Debugging log
                                    if (!data.timestamps || data.timestamps.length === 0) {
                                        console.warn("No data available for this range.");
                                        return;
                                    }
                                    updateChart(data);
                                })
                                .catch(error => console.error("Error fetching data:", error));
                        }

                        function updateChart(data) {
                            if (energyChart) {
                                energyChart.destroy();
                            }

                            energyChart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: data.timestamps.map(timestamp => {
                                        const date = new Date(timestamp);
                                        let hours = date.getHours();
                                        let ampm = hours >= 12 ? 'PM' : 'AM';
                                        hours = hours % 12 || 12;
                                        return `${hours} ${ampm}`;
                                    }),
                                    datasets: [
                                        {
                                            label: 'kWh',
                                            data: data.energyConsumption,
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'Watts',
                                            data: data.energyData.map(d => d.power_usage_watts),
                                            borderColor: 'rgba(255, 99, 132, 1)',
                                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'Voltage',
                                            data: data.energyData.map(d => d.voltage_volts),
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
                        }

                        // Fetch data when dropdown changes
                        document.getElementById('timeRange').addEventListener('change', function () {
                            fetchData(this.value);
                        });

                        // Load initial data
                        fetchData('24h');
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
        </div>
    </div>

    <div class="mt-6 max-w-6xl mx-auto pb-20 px-4">
        <h3 class="font-semibold text-2xl text-gray-800 dark:text-gray-200">Environment Data</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-white">
            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Temperature</h4>
                <canvas id="temperatureChart" class="mt-4"></canvas>
            </div>
            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Humidity</h4>
                <canvas id="humidityChart" class="mt-4"></canvas>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const temperatureCtx = document.getElementById('temperatureChart').getContext('2d');
                const humidityCtx = document.getElementById('humidityChart').getContext('2d');
                const temperatures = @json($temperatures);
                const humidity = @json($humidity);
                const timestamps = @json($envTimestamps);

                const formatTime = (timestamp) => {
                    const date = new Date(timestamp);
                    let hours = date.getHours();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12; // Convert to 12-hour format
                    return `${hours} ${ampm}`;
                };

                new Chart(temperatureCtx, {
                    type: 'line',
                    data: {
                        labels: timestamps.map(formatTime), // Format timestamps for x-axis
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: temperatures,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderWidth: 1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                new Chart(humidityCtx, {
                    type: 'line',
                    data: {
                        labels: timestamps.map(formatTime), // Format timestamps for x-axis
                        datasets: [{
                            label: 'Humidity (%)',
                            data: humidity,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderWidth: 1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</x-app-layout>
