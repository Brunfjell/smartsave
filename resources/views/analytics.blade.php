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
        <p class="text-l text-gray-800 dark:text-gray-200 leading-tight mt-3">
            {{ __("Details about energy consumption and production.") }}
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-white">
            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Energy Data</h4>
                    <canvas id="energyChart"></canvas>
            </div>
            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Card 2</h4>
                <p>Content for card 2.</p>
            </div>
            <div class="bg-gray-950 shadow-md rounded-lg p-4 mx-2">
                <h4 class="font-semibold">Card 3</h4>
                <p>Content for card 3.</p>
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
                                label: 'kWh Consumption',
                                data: energyConsumption,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderWidth: 1
                            },
                            {
                                label: 'Power Usage (Watts)',
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
</x-app-layout>
