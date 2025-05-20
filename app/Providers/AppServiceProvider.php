<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Section;
use App\Models\Item;
use App\Models\Tag;
use App\Policies\ProjectPolicy;
use App\Policies\SectionPolicy;
use App\Policies\ItemPolicy;
use App\Policies\TagPolicy;
use App\Repositories\ProjectRepository;
use App\Repositories\ProjectRepositoryInterface;
use App\Repositories\SectionRepository;
use App\Repositories\SectionRepositoryInterface;
use App\Repositories\ItemRepository;
use App\Repositories\ItemRepositoryInterface;
use App\Repositories\TagRepository;
use App\Repositories\TagRepositoryInterface;
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
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
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

        // Register Item Policy
        Gate::policy(Item::class, ItemPolicy::class);

        // Register Tag Policy
        Gate::policy(Tag::class, TagPolicy::class);
    }
}
