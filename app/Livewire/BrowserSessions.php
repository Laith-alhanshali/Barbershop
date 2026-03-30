<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class BrowserSessions extends Component
{
    public User $record;

    public function getSessionsProperty()
    {
        if (! $this->record->exists) {
            return collect();
        }

        return collect(
            DB::table('sessions')
                ->where('user_id', $this->record->id)
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function ($session) {
            return (object) [
                'agent' => $this->createAgent($session),
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });
    }

    protected function createAgent($session)
    {
        return tap(new \stdClass, function ($agent) use ($session) {
            $agent->platform = $this->platform($session->user_agent);
            $agent->browser = $this->browser($session->user_agent);
        });
    }

    protected function platform($userAgent)
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'windows')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os x')) {
            return 'Mac';
        }
        if (str_contains($userAgent, 'linux')) {
            return 'Linux';
        }
        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad') || str_contains($userAgent, 'ipod')) {
            return 'iOS';
        }
        if (str_contains($userAgent, 'android')) {
            return 'Android';
        }

        return 'Unknown';
    }

    protected function browser($userAgent)
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'chrome')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'firefox')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'safari')) {
            // Chrome also contains Safari, so check Chrome first
            return 'Safari';
        }
        if (str_contains($userAgent, 'edge')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'opera') || str_contains($userAgent, 'opr/')) {
            return 'Opera';
        }

        return 'Unknown';
    }

    public function logoutOtherBrowserSessions()
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user->can('delete', $this->record) && ! $user->hasRole('super_admin')) {
             // Basic permission check - though the UI is already protected
             return;
        }

        DB::table('sessions')
            ->where('user_id', $this->record->id)
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        Notification::make()
            ->title('Other browser sessions logged out.')
            ->success()
            ->send();
            
        $this->dispatch('sessions-cleared'); // Optional: for any frontend reactivity if needed
    }

    public function render()
    {
        return view('livewire.browser-sessions');
    }
}
