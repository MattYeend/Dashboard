<?php

namespace App\Observers;

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Mail::to($user->email)->send(
            new WelcomeEmail($user, $user->plainPassword)
        );
    }
}
