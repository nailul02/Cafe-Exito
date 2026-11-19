<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReconcileDatabaseCommand extends Command
{
    protected $signature = 'app:reconcile-database';

    protected $description = 'Disabled: use reviewed additive migrations instead of broad in-place reconciliation.';

    public function handle(): int
    {
        $this->error('Command disabled. The legacy implementation changes many tables and deletes migration history. Use additive migrations reviewed and tested on a database copy.');

        return self::FAILURE;
    }
}
