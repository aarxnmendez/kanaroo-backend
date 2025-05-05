<?php

namespace App\Providers;

use App\Repositories\ProjectRepository;
use App\Repositories\ProjectRepositoryInterface;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Models\Project;
use App\Policies\ProjectPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Register Project Policy
        \Illuminate\Support\Facades\Gate::policy(Project::class, ProjectPolicy::class);
    }
}
