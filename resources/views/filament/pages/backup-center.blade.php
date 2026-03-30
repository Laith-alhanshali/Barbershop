<x-filament::page>
    <x-filament::card>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold">
                Available Backups
            </h2>
            <span class="text-sm text-gray-500">
                {{ count($this->backups()) }} file(s)
            </span>
        </div>

        @if (count($this->backups()) === 0)
            <div class="text-center py-10 text-gray-500">
                No backups found.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($this->backups() as $backup)
                <br>
                    <div
                        class="grid grid-cols-12 items-center gap-x-6 gap-y-2
                               bg-white rounded-xl shadow-sm ring-1 ring-gray-100
                               px-6 py-4"
                    >
                        {{-- File name --}}
                        <div class="col-span-12 md:col-span-5 font-medium truncate">
                            {{ $backup['name'] }}
                        </div>

                        {{-- Date --}}
                        <div class="col-span-6 md:col-span-3 text-gray-600 whitespace-nowrap">
                            {{ $backup['date'] }}
                        </div>

                        {{-- Size --}}
                        <div class="col-span-6 md:col-span-2 text-gray-600 text-right whitespace-nowrap">
                            {{ number_format($backup['size'] / 1024 / 1024, 2) }} MB
                        </div>

                        {{-- Action --}}
                        <div class="col-span-12 md:col-span-2 text-right">
                            <x-filament::button
                                size="sm"
                                icon="heroicon-o-arrow-down-tray"
                                wire:click="download('{{ $backup['path'] }}')"
                            >
                                Download
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::card>
</x-filament::page>
