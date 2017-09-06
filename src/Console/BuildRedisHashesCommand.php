<?php

namespace AetherUpload\Console;

use Illuminate\Console\Command;
use AetherUpload\RedisHandler;

class BuildRedisHashesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aetherupload:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the correspondences between hashes and file storage paths in redis';

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
        RedisHandler::build();
        $this->info('done');
    }
}
