<?php

use App\Http\Controllers\Security\TwoFactorController;
use App\Http\Controllers\Security\UserSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorController::class, 'confirmSetup'])->name('two-factor.setup.confirm');
    Route::get('/two-factor/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge'])->name('two-factor.challenge.verify');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    Route::get('/security/sessions', [UserSessionController::class, 'index'])->name('security.sessions.index');
    Route::delete('/security/sessions/{sessionId}', [UserSessionController::class, 'destroy'])->name('security.sessions.destroy');
    Route::post('/security/sessions/revoke-others', [UserSessionController::class, 'destroyOthers'])->name('security.sessions.revoke-others');
});
