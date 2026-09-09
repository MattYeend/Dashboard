<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('billing:migrate-user-subscriptions {--dry-run}')]
#[Description('Move existing live subscriptions from their User owner to that user\'s organisation.')]
class MigrateUserSubscriptionsToOrganisations extends Command
{
    /**
     * Execute the console command.
     *
     * For each subscription still keyed to a user, resolves that user's
     * organisation and backfills organisation_id. Does not touch Stripe.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        Subscription::query()
            ->whereNull('organisation_id')
            ->whereNotNull('user_id')
            ->each(function (Subscription $subscription) use ($dryRun) {
                $user = User::find($subscription->user_id);

                if ($user === null) {
                    $this->error(
                        "User {$subscription->user_id} not found for subscription {$subscription->id}"
                    );

                    return;
                }

                $organisation = $user->organisations()->first();

                if ($organisation === null) {
                    $this->error(
                        "No organisation found for user {$user->id}, subscription {$subscription->id}"
                    );

                    return;
                }

                if ($dryRun) {
                    $this->info(
                        "Would move subscription {$subscription->id} to organisation {$organisation->id}"
                    );

                    return;
                }

                $subscription->forceFill([
                    'organisation_id' => $organisation->id,
                ])->save();

                $this->info(
                    "Moved subscription {$subscription->id} to organisation {$organisation->id}"
                );
            });

        return self::SUCCESS;
    }
}
