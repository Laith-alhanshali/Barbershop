<div class="space-y-6">
    @if ($this->sessions->isNotEmpty())
        <div class="space-y-3">
            @foreach ($this->sessions as $session)
                @php
                    $platform = $session->agent->platform ?? '';
                    $isMobile = in_array($platform, ['iOS', 'Android']);
                @endphp

                <div
                    class="flex items-start gap-3 rounded-xl border border-gray-200/60 bg-white/5 p-4
                           dark:border-white/10 dark:bg-white/5"
                >
                    {{-- Icon --}}
                    <div class="shrink-0 pt-0.5">
                        <x-filament::icon
                            :icon="$isMobile ? 'heroicon-o-device-phone-mobile' : 'heroicon-o-computer-desktop'"
                            class="h-6 w-6 text-gray-400"
                        />
                    </div>

                    {{-- Text --}}
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $session->agent->platform ?? 'Unknown' }} - {{ $session->agent->browser ?? 'Unknown' }}
                        </div>

                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ $session->ip_address ?? '-' }},
                            @if ($session->is_current_device)
                                <span class="font-semibold text-primary-600 dark:text-primary-400">
                                    {{ __('This device') }}
                                </span>
                            @else
                                {{ __('Last active') }} {{ $session->last_active ?? '-' }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('No sessions found.') }}
        </div>
    @endif

</div>
