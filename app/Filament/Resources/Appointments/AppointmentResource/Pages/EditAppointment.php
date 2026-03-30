<?php

namespace App\Filament\Resources\Appointments\AppointmentResource\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;


class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function isFullyLocked(): bool
    {
        return in_array($this->record->status, ['done', 'cancelled'], true);
    }

    protected function isStatusOnly(): bool
    {
        return $this->record->status === 'confirmed';
    }

    


    protected function getHeaderActions(): array
{
    $record = $this->record;

    return [
        Actions\Action::make('open_invoice')
            ->label(__('admin.appointments.actions.open_invoice'))
            ->icon('heroicon-o-document-text')
            ->color('info')
            ->visible(fn () =>
                $this->isFullyLocked()
                && $record->invoice()->exists()
                && Route::has('filament.admin.resources.invoices.view')
            )
            ->url(fn () => \App\Filament\Resources\Invoices\InvoiceResource::getUrl('view', [
                'record' => $record->invoice,
            ])),

        Actions\Action::make('print_invoice')
            ->label(__('admin.appointments.actions.print_invoice'))
            ->icon('heroicon-o-printer')
            ->color('success')
            ->visible(fn () =>
                $this->isFullyLocked()
                && $record->invoice()->exists()
            )
            ->url(fn () => route('invoices.print', $record->invoice))
            ->openUrlInNewTab(),

        Actions\Action::make('generate_invoice')
            ->label(__('admin.appointments.actions.generate_invoice'))
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->visible(fn () =>
                // ✅ يظهر فقط إذا confirmed أو done
                in_array($record->status, ['confirmed', 'done'], true)
                // ✅ ما فيه فاتورة
                && ! $record->invoice()->exists()
                // ✅ الموعد مو محذوف
                && ! $record->trashed()
                // ✅ لو ملغي ما يظهر
                && $record->status !== 'cancelled'
            )
            ->requiresConfirmation()
            ->modalHeading(__('admin.appointments.actions.generate_invoice'))
            ->modalDescription(__('admin.appointments.actions.generate_invoice_description'))
            ->action(function () {
                // نفس كود إنشاء الفاتورة عندك (بدون تغيير)
                $appointment = $this->record;

                DB::transaction(function () use ($appointment) {
                    $services = $appointment->services;
                    $subtotal = 0;
                    $items = [];

                    foreach ($services as $service) {
                        $unitPrice = $service->pivot->price_at_booking;
                        $lineTotal = $unitPrice; // qty = 1
                        $subtotal += $lineTotal;

                        $items[] = [
                            'service_id' => $service->id,
                            'name' => $service->name,
                            'qty' => 1,
                            'duration_min' => $service->pivot->duration_min_at_booking,
                            'unit_price' => $unitPrice,
                            'line_total' => $lineTotal,
                        ];
                    }

                    $invoice = Invoice::create([
                        'appointment_id' => $appointment->id,
                        'customer_id' => $appointment->customer_id,
                        'barber_id' => $appointment->barber_id,
                        'number' => Invoice::generateNumber(),
                        'status' => 'unpaid',
                        'subtotal' => $subtotal,
                        'coupon_id' => $appointment->coupon_id,
                        'discount'  => (float) ($appointment->discount ?? 0),
                        'total'     => max(0, $subtotal - $appointment->discount),
                        'tax' => 0,
                        'created_by' => Auth::id(),
                    ]);

                    foreach ($items as $item) {
                        $invoice->items()->create($item);
                    }

                    // ✅ Apply coupon discount snapshot (no used_count increment here)
                    $discount = 0;

                    $coupon = $appointment->coupon; // requires relationship appointment->coupon()
                    if ($coupon) {
                        $now = now();

                        $isValid =
                            $coupon->is_active
                            && (is_null($coupon->starts_at) || $coupon->starts_at->lte($now))
                            && (is_null($coupon->expires_at) || $coupon->expires_at->gte($now))
                            && (is_null($coupon->max_uses) || $coupon->used_count < $coupon->max_uses)
                            && (is_null($coupon->min_subtotal) || (float) $subtotal >= (float) $coupon->min_subtotal);

                        if ($isValid) {
                            if ($coupon->type === 'percent') {
                                $percent = max(0, min(100, (float) $coupon->value));
                                $discount = round($subtotal * ($percent / 100), 2);
                            } else { // fixed
                                $discount = round(min((float) $coupon->value, (float) $subtotal), 2);
                            }
                        } else {
                            // إذا تحب: افصل الكوبون عن الموعد أو اتركه لكن لا تطبقه
                            // $appointment->update(['coupon_id' => null]);
                        }
                    }

                    $total = round($subtotal - $discount, 2);

                    $invoice->update([
                        'discount' => $discount,
                        'total' => $total,
                    ]);

                });

                Notification::make()
                    ->title(__('admin.appointments.messages.invoice_generated'))
                    ->success()
                    ->send();

                $invoice = $appointment->fresh()->invoice;

                return redirect()->to(
                    \App\Filament\Resources\Invoices\InvoiceResource::getUrl('edit', ['record' => $invoice])
                );
            }),

        Actions\DeleteAction::make()
            ->visible(fn () =>
                // نحجب الحذف إذا fully locked
                ! $this->isFullyLocked()
            ),
    ];
}


    protected function mutateFormDataBeforeSave(array $data): array
{
    $record = $this->record;

    /** @var User|null $user */
    $user = Auth::user();
    $isSuper = $user?->hasRole('super_admin') ?? false;

    // ✅ 1) done / cancelled => ممنوع أي تعديل نهائيًا للجميع
    if (in_array($record->status, ['done', 'cancelled'], true)) {
        $msg = __('admin.appointments.messages.locked_done_cancelled');

        $this->addError('status', $msg);

        Notification::make()
            ->title($msg)
            ->danger()
            ->send();

        $this->halt();
    }

    // ✅ 2) confirmed => status فقط (الباقي يتجاهل)
    if ($record->status === 'confirmed') {
        return [
            'status'     => $data['status'] ?? $record->status,
            'coupon_id' => $data['coupon_id'] ?? $record->coupon_id,
            'discount'  => $data['discount'] ?? $record->discount,
            'updated_by' => Auth::id(),
        ];
    }

    // ✅ 3) باقي الحالات => تعديل كامل + حساب end_at + منع التداخل

    $serviceIds = $data['services']
        ?? $record->services()->pluck('services.id')->toArray();

    if (empty($serviceIds)) {
        $msg = __('admin.appointments.validation.services_required');

        $this->addError('services', $msg);

        Notification::make()
            ->title($msg)
            ->danger()
            ->send();

        $this->halt();
    }

    $startAt = Carbon::parse($data['start_at'] ?? $record->start_at);

    // غير super_admin ممنوع يحجز وقت أقدم من ساعة من الآن
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
    $data['updated_by'] = Auth::id();

    $barberId = (int) ($data['barber_id'] ?? $record->barber_id);
    $this->validateNoOverlap($barberId, $startAt, $endAt, $record->id);

    return $data;
}

    protected function afterSave(): void
    {
        $appointment = $this->record;

        if (in_array($appointment->status, ['confirmed', 'done', 'cancelled'])) {
            return;
        }

        $serviceIds = $this->data['services'] ?? [];
        if (empty($serviceIds)) return;

        $services = Service::whereIn('id', $serviceIds)->get();

        $syncData = $services->mapWithKeys(fn ($service) => [
            $service->id => [
                'price_at_booking' => $service->price,
                'duration_min_at_booking' => $service->duration_min,
            ],
        ])->toArray();

        $appointment->services()->sync($syncData);
    }

    protected function validateNoOverlap(
    int $barberId,
    Carbon $startAt,
    Carbon $endAt,
    ?int $ignoreId = null
): void {
    $query = Appointment::where('barber_id', $barberId)
        ->whereIn('status', ['pending', 'confirmed'])
        ->where(function ($q) use ($startAt, $endAt) {
            $q->where('start_at', '<', $endAt)
              ->where('end_at',   '>', $startAt);
        });

    if ($ignoreId) {
        $query->where('id', '!=', $ignoreId);
    }

    if ($query->exists()) {
        $msg = __('admin.appointments.validation.overlap');

        $this->addError('start_at', $msg);

        Notification::make()
            ->title($msg)
            ->danger()
            ->send();

        $this->halt();
    }
}

}
