<?php

namespace AetherUpload\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use AetherUpload\ConfigMapper;

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

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $invalidHeaders = [];
        $invalidFiles = [];
        $dueTime = strtotime('-' . $this->argument('days') . ' day');
        $rootDir = ConfigMapper::get('root_dir');

        try {
            $headers = Storage::disk(ConfigMapper::get('header_storage_disk'))->files($rootDir . DIRECTORY_SEPARATOR . '_header');

            foreach ( $headers as $header ) {

                if ( pathinfo($header, PATHINFO_EXTENSION) !== '' ) {
                    continue;
                }

                $createTime = substr(basename($header), 0, 10);

                if ( $createTime < $dueTime ) {
                    $invalidHeaders[] = $header;
                }
            }

            Storage::disk(ConfigMapper::get('header_storage_disk'))->delete($invalidHeaders);

            $this->info(count($invalidHeaders) . ' invalid headers have been deleted.');

            $groupDirs = array_map(function ($v) {
                return $v['group_dir'];
            }, Config::get('aetherupload.groups'));

            foreach ( $groupDirs as $groupDir ) {
                $subDirNames = Storage::directories($rootDir . DIRECTORY_SEPARATOR . $groupDir);

                foreach ( $subDirNames as $subDirName ) {
                    $files = Storage::files($subDirName);

                    foreach ( $files as $file ) {

                        if ( pathinfo($file, PATHINFO_EXTENSION) !== 'part' ) {
                            continue;
                        }

                        $createTime = substr($fileName = basename($file, '.part'), 0, 10);

                        if ( $createTime < $dueTime ) {
                            $invalidFiles[] = $file;
                        }
                    }
                }
            }

            Storage::delete($invalidFiles);

            $this->info(count($invalidFiles) . ' invalid files have been deleted.');
            $this->info('Done.');

        } catch ( \Exception $e ) {

            $this->error($e->getMessage());
        }

    }
}
