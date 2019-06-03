<?php

namespace AetherUpload\Console;

use AetherUpload\ConfigMapper;
use AetherUpload\SavedPathResolver;
use AetherUpload\Util;
use Illuminate\Console\Command;
use AetherUpload\RedisSavedPath;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $savedPathArr = [];

        try {

            RedisSavedPath::deleteAll();

            foreach ( Config::get('aetherupload.groups') as $groupName => $group ) {
                $subDirNames = Storage::directories(ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $group['group_dir']);

                foreach ( $subDirNames as $subDirName ) {
                    $fileNames = Storage::files($subDirName);

                    foreach ( $fileNames as $fileName ) {
                        if ( pathinfo($fileName, PATHINFO_EXTENSION) === 'part' ) {
                            continue;
                        }

                        $savedPathArr[RedisSavedPath::getKey($groupName, pathinfo($fileName, PATHINFO_FILENAME))] = SavedPathResolver::encode($group['group_dir'], basename($subDirName), basename($fileName));

                    }
                }
            }

            RedisSavedPath::setMulti($savedPathArr);

            $this->info(count($savedPathArr) . ' items have been set in Redis.');
            $this->info('Done.');

        } catch ( \Exception $e ) {

            $this->error($e->getMessage());
        }

    }
}
