<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Section;
use App\Policies\ProjectPolicy;
use App\Policies\SectionPolicy;
use App\Repositories\ProjectRepository;
use App\Repositories\ProjectRepositoryInterface;
use App\Repositories\SectionRepository;
use App\Repositories\SectionRepositoryInterface;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
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
        Gate::policy(Project::class, ProjectPolicy::class);

        // Register Section Policy
        Gate::policy(Section::class, SectionPolicy::class);
    }
}
