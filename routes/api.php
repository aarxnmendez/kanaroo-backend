<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\AuthenticatedUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SectionController;

// Public API routes (no authentication required)
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);

// Special routes
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Protected routes with Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store']);
    Route::get('/user', AuthenticatedUserController::class);

    // Project routes
    Route::apiResource('projects', ProjectController::class);

    // Section routes (nested under projects)
    Route::prefix('projects/{project}')->group(function () {
        Route::get('sections', [SectionController::class, 'index'])->name('projects.sections.index');
        Route::post('sections', [SectionController::class, 'store'])->name('projects.sections.store');
        Route::get('sections/{section}', [SectionController::class, 'show'])->name('projects.sections.show');
        Route::match(['put', 'patch'], 'sections/{section}', [SectionController::class, 'update'])->name('projects.sections.update');
        Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('projects.sections.destroy');
        Route::post('sections/reorder', [SectionController::class, 'reorder'])->name('projects.sections.reorder');
    });
});
