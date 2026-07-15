<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Industry;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Plan;
use App\Models\SubjectArea;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\AddressPolicy;
use App\Policies\ApiTokenPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ContactPolicy;
use App\Policies\IndustryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\OrderStatusPolicy;
use App\Policies\PlanPolicy;
use App\Policies\SubjectAreaPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TaskStatusPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Cashier\Cashier;
use Laravel\Sanctum\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    private const DESTRUCTIVE_COMMANDS = [
        'migrate:fresh', // Drops all tables
        'migrate:refresh', // Rolls back all migrations and re-runs them
        'migrate:reset', // Rolls back all migrations
        'migrate:rollback', // Rolls back a batch of migrations
        'db:wipe', // Drops all databases
    ];

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
        Gate::policy(OrderStatus::class, OrderStatusPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Industry::class, IndustryPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(PersonalAccessToken::class, ApiTokenPolicy::class);
        Gate::policy(Address::class, AddressPolicy::class);
        Gate::policy(SubjectArea::class, SubjectAreaPolicy::class);

        Relation::morphMap([
            'App\Models\User' => User::class,
            'App\Models\Company' => Company::class,
            'App\Models\Task' => Task::class,
        ]);

        User::observe(UserObserver::class);

        Cashier::useSubscriptionModel(Subscription::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

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

        $this->preventDestructiveCommandsInProtectedEnvironments();
    }

    /**
     * Conditionally prevent destructive commands in protected environments.
     */
    protected function preventDestructiveCommandsInProtectedEnvironments(): void
    {
        if (! app()->environment('production', 'staging', 'qa')) {
            return;
        }

        array_map(
            fn ($command) => $this->disableDestructiveCommand($command),
            self::DESTRUCTIVE_COMMANDS
        );
    }

    /**
     * Disable a specific destructive command.
     */
    private function disableDestructiveCommand(string $command): void
    {
        Artisan::command($command, function () use ($command) {
            /** @var Command $this */
            $this->error(
                "The '{$command}' command is disabled in this
                environment for safety."
            );
        });
    }
}
