<?php

namespace AetherUpload\Console;

use AetherUpload\ResourceHandler;
use Illuminate\Console\Command;
use AetherUpload\ResourceHashHandler;

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
        ResourceHashHandler::deleteAllHashes();

        $groupNames = array_keys(config('aetherupload.GROUPS'));
        $rootDir = config('aetherupload.ROOT_DIR');

        foreach ( $groupNames as $groupName ) {
            $subDirNames = $this->resourceHandler->directories($rootDir . DIRECTORY_SEPARATOR . $groupName);

            foreach ( $subDirNames as $subDirName ) {
                $fileNames = $this->resourceHandler->files($subDirName);

                foreach ( $fileNames as $fileName ) {
                    if ( pathinfo($fileName, PATHINFO_EXTENSION) === 'part' ) {
                        continue;
                    }

                    ResourceHashHandler::setOneHash($groupName.pathinfo($fileName, PATHINFO_FILENAME), $groupName . "_" . basename($subDirName) . "_" . basename($fileName));

                }
            }
        }

        $this->info('done');
    }
}
