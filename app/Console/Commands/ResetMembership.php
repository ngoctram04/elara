<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetMembership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-membership';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
\App\Models\User::query()->update([
'yearly_spent'    => 0,
'member_level'    => 'bronze',
'membership_year' => now()->year
]);

$this->info('Membership reset completed');

}

}