<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-quotes')]
#[Description('Command description')]
class ExpireQuotes extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
