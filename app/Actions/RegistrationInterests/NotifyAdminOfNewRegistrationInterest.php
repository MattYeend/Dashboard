<?php

namespace App\Actions\RegistrationInterests;

use App\Models\RegistrationInterest;
use App\Models\User;
use App\Notifications\NewRegistrationInterestNotification;
use Illuminate\Support\Facades\Notification;

class NotifyAdminOfNewRegistrationInterest
{
    /**
     * Notify all admin and super admin users of a new registration interest.
     */
    public function execute(RegistrationInterest $interest): void
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

        Notification::send($admins, new NewRegistrationInterestNotification($interest));
    }
}
