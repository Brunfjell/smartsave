@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-sky-500 dark:focus:border-sky-500 focus:ring-sky-500 dark:focus:ring-sky-500 rounded-md shadow-sm']) }}>
