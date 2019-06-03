<?php

namespace AetherUpload\Console;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Console\Command;

class ListGroupDirectoryCommand extends Command
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
    protected $description = 'List and create the directories for the groups';


    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $rootDir = Config::get('aetherupload.root_dir');

        try {

            if ( ! Storage::exists($rootDir) ) {
                Storage::makeDirectory($rootDir . DIRECTORY_SEPARATOR . '_header');
                $this->info('Root directory "' . $rootDir . '" has been created.');
            }

            $directories = array_map(function ($directory) {
                return basename($directory);
            }, Storage::directories($rootDir));

            $groupDirs = array_map(function ($v) {
                return $v['group_dir'];
            }, Config::get('aetherupload.groups'));

            foreach ( $groupDirs as $groupDir ) {
                if ( in_array($groupDir, $directories) ) {
                    continue;
                } else {
                    if ( Storage::makeDirectory($rootDir . DIRECTORY_SEPARATOR . $groupDir) ) {
                        $this->info('Directory "' . $rootDir . DIRECTORY_SEPARATOR . $groupDir . '" has been created.');
                    } else {
                        $this->error('Fail to create directory "' . $rootDir . DIRECTORY_SEPARATOR . $groupDir . '".');
                    }
                }
            }

            $this->info('Group-Directory List:');

            foreach ( Config::get('aetherupload.groups') as $groupName => $groupArr ) {
                if ( Storage::exists($rootDir . DIRECTORY_SEPARATOR . $groupArr['group_dir']) ) {
                    $this->info($groupName . '-' . $groupArr['group_dir']);
                }
            }

        } catch ( \Exception $e ) {

            $this->error($e->getMessage());
        }

    }
}
