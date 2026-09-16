<?php

namespace App\Console\Commands;

use App\Models\Organisation;
use App\Models\User;
use App\Services\Organisations\OffboardingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('organisations:hard-delete-expired')]
#[Description('Permanently delete organisations past their soft-delete retention window.')]
class HardDeleteExpiredOrganisations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(OffboardingService $offboardingService): int
    {
        $systemActor = User::query()
            ->where('email', config('mail.system_user_email'))
            ->firstOrFail();

        $expired = Organisation::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->get();

        foreach ($expired as $organisation) {
            $offboardingService->hardDelete(
                $organisation,
                $systemActor
            );

            $this->info(
                "Hard-deleted organisation #{$organisation->id} "
                . "({$organisation->name})."
            );
        }

        return self::SUCCESS;
    }
}
