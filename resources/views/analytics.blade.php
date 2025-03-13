<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Analytics') }}
        </h2>
        <p class="text-l text-gray-800 dark:text-gray-200 leading-tight mt-3">
            {{ __("Here are your analytics data") }}
        </p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Here are your analytics data.") }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("Here are your analytics data.") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
