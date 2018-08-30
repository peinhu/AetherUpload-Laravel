<?php

namespace AetherUpload\Console;

use AetherUpload\ResourceHandler;
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

    protected $resourceHandler;

    /**
     * Create a new command instance.
     * @param ResourceHandler $resourceHandler
     */
    public function __construct(ResourceHandler $resourceHandler)
    {
        parent::__construct();
        $this->resourceHandler = $resourceHandler;

    }


    public function handle()
    {
        $groupNames = array_keys(config('aetherupload.GROUPS'));

        $rootDir = config('aetherupload.ROOT_DIR');

        $directories = array_map(function ($item) {
            return basename($item);
        }, $this->resourceHandler->directories($rootDir));

        foreach ( $groupNames as $groupName ) {
            if ( in_array($groupName, $directories) ) {
                continue;
            } else {
                if ( $this->resourceHandler->makeDirectory($rootDir . DIRECTORY_SEPARATOR . $groupName) ) {
                    $this->info('Directory "' . $rootDir . DIRECTORY_SEPARATOR . $groupName . '" is created.');
                } else {
                    $this->error('Fail to create directory "' . $rootDir . DIRECTORY_SEPARATOR . $groupName . '".');
                }
            }
        }

        $this->info('Group List:');

        foreach ( $groupNames as $groupName ) {
            if ( $this->resourceHandler->exists($rootDir . DIRECTORY_SEPARATOR . $groupName) ) {
                $this->info($groupName);
            }
        }

    }
}
