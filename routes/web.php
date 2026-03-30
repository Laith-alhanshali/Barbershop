<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['en', 'ar'])) abort(404);

    if (Auth::id()) {
        User::whereKey(Auth::id())->update(['locale' => $locale]);
    }

    app()->setLocale($locale);

    return back();
})->name('lang.switch');


Route::get('/invoices/{invoice}/print', \App\Http\Controllers\InvoicePrintController::class)
    ->name('invoices.print')
    ->middleware('auth');

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
