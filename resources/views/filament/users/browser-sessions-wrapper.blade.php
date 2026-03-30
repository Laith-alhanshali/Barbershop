<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-4">
        <h3 class="text-lg font-medium tracking-tight text-gray-950 dark:text-white">
            {{ __('Browser Sessions') }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('Manage and log out your active sessions on other browsers and devices.') }}
        </p>
    </div>

    @livewire('browser-sessions', ['record' => $record])
</div>
