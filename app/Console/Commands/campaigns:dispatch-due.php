<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:campaigns:dispatch-due')]
#[Description('Command description')]
class campaigns:dispatch-due extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
