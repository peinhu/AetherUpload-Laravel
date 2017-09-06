<?php

namespace AetherUpload\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aetherupload:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'An easy way for vendor:publish';

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
        $this->call('vendor:publish', [
            '--tag' => 'aetherupload', '--force' => true
        ]);
    }
}
