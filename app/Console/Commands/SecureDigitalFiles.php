<?php

namespace App\Console\Commands;

use App\Support\DigitalFiles;
use Illuminate\Console\Command;

class SecureDigitalFiles extends Command
{
    protected $signature = 'products:secure-files';

    protected $description = 'Move digital product files from the public folder to the private uploads folder';

    public function handle(): int
    {
        $moved = DigitalFiles::moveAllLegacy();
        $this->info("Moved {$moved} file(s) to " . DigitalFiles::dir());

        return self::SUCCESS;
    }
}
