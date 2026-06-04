<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Policies\ContactPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TaskStatusPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Currently Empty
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(TaskStatus::class, TaskStatusPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        RateLimiter::for('api', function (Request $request) {
            $userId = $request->user()?->id;

            return Limit::perMinute(60)->by($userId ?? $request->ip());
        });
    }
}
