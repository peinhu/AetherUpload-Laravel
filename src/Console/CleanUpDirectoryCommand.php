<?php

namespace AetherUpload\Console;

use Illuminate\Console\Command;
use AetherUpload\ResourceHandler;
use AetherUpload\HeaderHandler;

class CleanUpDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aetherupload:clean {days=2 : The number of days from today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove partial files which are created a few days ago';

    protected $resourceHandler, $headerHandler;

    /**
     * Create a new command instance.
     * @param HeaderHandler $headerHandler
     * @param ResourceHandler $resourceHandler
     */
    public function __construct(HeaderHandler $headerHandler, ResourceHandler $resourceHandler)
    {
        parent::__construct();
        $this->headerHandler = $headerHandler;
        $this->resourceHandler = $resourceHandler;
    }

    public function handle()
    {
        $dueTime = strtotime("-".$this->argument('days')." day");
        $uploadPath = config('aetherupload.ROOT_DIR');

        $headerNames = $this->headerHandler->files($uploadPath . DIRECTORY_SEPARATOR . '_header');

        foreach ( $headerNames as $headName ) {

            if ( pathinfo($headName, PATHINFO_EXTENSION) !== '' ) {
                continue;
            }

            $createTime = substr(basename($headName), 0, 10);

            if ( $createTime < $dueTime ) {
                $this->headerHandler->deleteHeader(basename($headName));
            }
        }

        $groupNames = array_keys(config('aetherupload.GROUPS'));

        foreach ( $groupNames as $groupName ) {
            $subDirNames = $this->resourceHandler->directories($uploadPath . DIRECTORY_SEPARATOR . $groupName);

            foreach ( $subDirNames as $subDirName ) {
                $files = $this->resourceHandler->files($subDirName);

                foreach ( $files as $file ) {

                    if ( pathinfo($file, PATHINFO_EXTENSION) !== 'part' ) {
                        continue;
                    }

                    $createTime = substr($fileBaseName = basename($file, '.part'), 0, 10);

                    if ( $createTime < $dueTime ) {
                        $this->resourceHandler->deleteResource($fileBaseName, basename($subDirName), $groupName);
                    }
                }
            }

        }

        $this->info('done');
    }
}
