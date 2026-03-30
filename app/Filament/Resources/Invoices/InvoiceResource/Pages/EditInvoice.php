<?php

namespace App\Filament\Resources\Invoices\InvoiceResource\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Forms;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;


class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isSuper = $user?->hasRole('super_admin') ?? false;

        return [
            Actions\Action::make('print')
                ->label(__('admin.invoices.actions.print'))
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('invoices.print', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('open_appointment')
                ->label(__('admin.invoices.actions.open_appointment'))
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->visible(fn () => $this->record->appointment_id)
                ->url(fn () => \App\Filament\Resources\Appointments\AppointmentResource::getUrl('edit', ['record' => $this->record->appointment])),

            Actions\Action::make('mark_as_paid')
            ->label(__('admin.invoices.actions.mark_as_paid'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn () => $this->record->status !== 'paid')
            ->requiresConfirmation()
            ->modalHeading(__('admin.invoices.actions.mark_as_paid'))
            ->modalDescription(__('admin.invoices.actions.mark_as_paid_description'))
            
            ->form([
                Forms\Components\Select::make('payment_method')
                    ->label(__('admin.invoices.fields.payment_method'))
                    ->options([
                        'cash' => __('admin.invoices.payment_methods.cash'),
                        'card' => __('admin.invoices.payment_methods.card'),
                        'transfer' => __('admin.invoices.payment_methods.transfer'),
                    ])
                    ->required(),

        Forms\Components\Textarea::make('payment_note')
            ->label(__('admin.invoices.fields.payment_note'))
            ->rows(3)
            ->placeholder(__('admin.invoices.helpers.payment_note'))
            ->nullable(),
            ])
            ->action(function (array $data) {
            $invoice = $this->record;

            DB::transaction(function () use ($invoice, $data) {
                $note = trim((string) ($data['payment_note'] ?? ''));

                // ✅ 1) Increment coupon used_count ONLY when paid
                if ($invoice->coupon_id) {
                    $coupon = \App\Models\Coupon::query()
                        ->lockForUpdate()
                        ->find($invoice->coupon_id);

                    if (! $coupon) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'payment_method' => __('admin.coupons.messages.not_found'),
                        ]);
                    }

                    $now = now();

                    if (! $coupon->is_active) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'payment_method' => __('admin.coupons.messages.inactive'),
                        ]);
                    }

                    if ($coupon->starts_at && $coupon->starts_at->gt($now)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'payment_method' => __('admin.coupons.messages.not_started'),
                        ]);
                    }

                    if ($coupon->expires_at && $coupon->expires_at->lt($now)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'payment_method' => __('admin.coupons.messages.expired'),
                        ]);
                    }

                    if (! is_null($coupon->max_uses) && $coupon->used_count >= $coupon->max_uses) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'payment_method' => __('admin.coupons.messages.limit_reached'),
                        ]);
                    }

                    if (! is_null($coupon->min_subtotal) && (float) $invoice->subtotal < (float) $coupon->min_subtotal) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'payment_method' => __('admin.coupons.messages.min_subtotal_not_met'),
                        ]);
                    }

                    $coupon->increment('used_count');
                }

                // ✅ 2) Mark invoice as paid (keep your notes behavior)
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $data['payment_method'],
                    'notes' => $note !== ''
                        ? trim(($invoice->notes ? $invoice->notes . "\n\n" : '') . $note)
                        : $invoice->notes,
                ]);
            });

            Notification::make()
                ->title(__('admin.invoices.messages.marked_as_paid'))
                ->success()
                ->send();
        }),



            Actions\Action::make('mark_as_unpaid')
                ->label(__('admin.invoices.actions.mark_as_unpaid'))
                ->icon('heroicon-o-x-circle')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'paid' && $isSuper)
                ->requiresConfirmation()
                ->modalHeading(__('admin.invoices.actions.mark_as_unpaid'))
                ->modalDescription(__('admin.invoices.actions.mark_as_unpaid_description'))
                ->action(function () {
                    $this->record->update([
                        'status' => 'unpaid',
                        'paid_at' => null,
                        'payment_method' => null,
                    ]);

                    Notification::make()
                        ->title(__('admin.invoices.messages.marked_as_unpaid'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
