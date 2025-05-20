<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Http\Request;

// Public API routes (no authentication required)
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');

// Special routes
Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Protected routes with Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user.info');

    // Project routes
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.add');
    Route::patch('projects/{project}/members/{user}', [ProjectController::class, 'updateMemberRole'])->name('projects.members.updateRole');
    Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');
    Route::delete('projects/{project}/leave', [ProjectController::class, 'leave'])->name('projects.leave');
    Route::post('projects/{project}/transfer-ownership', [ProjectController::class, 'transferOwnership'])->name('projects.transferOwnership');

    // Sections routes
    Route::apiResource('projects.sections', SectionController::class)->shallow();
    Route::post('projects/{project}/sections/reorder', [SectionController::class, 'reorder'])->name('projects.sections.reorder');

    // Items routes
    Route::apiResource('projects.sections.items', ItemController::class)->shallow();
    Route::post('projects/{project}/sections/{section}/items/reorder', [ItemController::class, 'reorder'])->name('projects.sections.items.reorder');

    // Tag routes
    Route::apiResource('projects.tags', TagController::class)->shallow();
});
