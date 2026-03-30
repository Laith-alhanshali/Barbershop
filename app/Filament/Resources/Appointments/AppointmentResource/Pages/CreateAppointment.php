<?php

namespace App\Filament\Resources\Appointments\AppointmentResource\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\User;


class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $serviceIds = $data['services'] ?? [];
        if (empty($serviceIds)) {
            $this->addError('services', __('admin.appointments.validation.services_required'));

            Notification::make()
                ->title(__('admin.appointments.validation.services_required'))
                ->danger()
                ->send();

            $this->halt();
        }

        $startAt = Carbon::parse($data['start_at']);

        /** @var User|null $user */
        $user = Auth::user();
        $isSuper = $user?->hasRole('super_admin') ?? false;

        if (! $isSuper && $startAt->lt(now()->subHour())) {
            $msg = __('admin.appointments.validation.past_date_not_allowed');

            $this->addError('start_at', $msg);

            Notification::make()
                ->title($msg)
                ->danger()
                ->send();

            $this->halt();
        }

        $totalDuration = (int) Service::whereIn('id', $serviceIds)->sum('duration_min');
        $endAt = (clone $startAt)->addMinutes($totalDuration);

        $data['end_at'] = $endAt->format('Y-m-d H:i:s');
        $data['created_by'] ??= Auth::id();

        // 👇 بدل throw .. نستخدم notify + addError
        if ($this->hasOverlap($data['barber_id'], $startAt, $endAt)) {
            $msg = __('admin.appointments.validation.overlap');

            $this->addError('start_at', $msg);

            Notification::make()
                ->title($msg)
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }


    protected function afterCreate(): void
    {
        /** @var Appointment $appointment */
        $appointment = $this->record;
        $serviceIds = $this->data['services'] ?? [];

        $services = Service::whereIn('id', $serviceIds)->get();

        $syncData = $services->mapWithKeys(fn ($service) => [
            $service->id => [
                'price_at_booking' => $service->price,
                'duration_min_at_booking' => $service->duration_min,
            ],
        ])->toArray();

        $appointment->services()->sync($syncData);
    }

    protected function hasOverlap(int $barberId, Carbon $startAt, Carbon $endAt, ?int $ignoreId = null): bool
{
    $query = Appointment::where('barber_id', $barberId)
        ->whereIn('status', ['pending', 'confirmed'])
        ->where(function ($q) use ($startAt, $endAt) {
            $q->where('start_at', '<', $endAt)
              ->where('end_at',   '>', $startAt);
        });

    if ($ignoreId) {
        $query->where('id', '!=', $ignoreId);
    }

    return $query->exists();
}
}
