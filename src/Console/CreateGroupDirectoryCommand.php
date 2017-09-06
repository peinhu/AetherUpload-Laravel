<?php

namespace AetherUpload\Console;

use Illuminate\Console\Command;

class CreateGroupDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aetherupload:groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create directories for the new groups in configuration';

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
        $config = config('aetherupload');
        $groupNames = array_keys($config['GROUPS']);
        $directories = scandir($config['UPLOAD_PATH']);
        foreach ( $groupNames as $groupName ) {
            $this->info($groupName);
            if ( in_array($groupName, $directories) ) {
                continue;
            } else {
                if ( @mkdir($config['UPLOAD_PATH'] . DIRECTORY_SEPARATOR . $groupName, 0755) ) {
                    $this->info('directory "' . $config['UPLOAD_PATH'] . DIRECTORY_SEPARATOR . $groupName . '" is created');
                } else {
                    $this->error('fail to create directory "' . $config['UPLOAD_PATH'] . DIRECTORY_SEPARATOR . $groupName . '"');
                }
            }
        }

    }
}
