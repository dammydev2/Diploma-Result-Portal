<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/admin')->name('dashboard');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('page.settings.security'),
        'manage' => route('page.settings.security'),
    ]);
})->name('well-known.passkeys');
