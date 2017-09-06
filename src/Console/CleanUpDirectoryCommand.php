<?php

namespace AetherUpload\Console;

use Illuminate\Console\Command;
use AetherUpload\ResourceHandler;

class CleanUpDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aetherupload:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove partial files which are created a few days ago';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ResourceHandler::cleanUpDir();
        $this->info('done');
    }
}
