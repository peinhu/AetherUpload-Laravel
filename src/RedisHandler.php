<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Redis;
use Predis\Connection\ConnectionException;

class RedisHandler
{
    /**
     * @param $fileHash
     * @return bool
     */
    public static function hashExists($fileHash)
    {
        $result = false;

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $result = Redis::hexists(config('aetherupload.REDIS_KEY'), $fileHash);
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }

    /**
     * @param $fileHash
     * @return string
     */
    public static function getFilePathByHash($fileHash)
    {
        $filePath = "";

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $filePath = Redis::hget(config('aetherupload.REDIS_KEY'), $fileHash);
            } catch ( ConnectionException $e ) {

            }
        }

        return $filePath;
    }

    /**
     * set or overwrite a hash
     * @param $fileHash
     * @param $savedPath
     * @return bool
     */
    public static function setOneHash($fileHash, $savedPath)
    {
        $result = false;

        if(class_exists('\Predis\Connection\ConnectionException')) {
            try {
                $result = Redis::hset(config('aetherupload.REDIS_KEY'), $fileHash, $savedPath);
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }

    /**
     * build the correspondences between hashes and file storage paths in redis
     */
    public static function build()
    {
        self::deleteAllHashes();

        $groupNames = array_keys(config('aetherupload.GROUPS'));
        $uploadPath = config('aetherupload.UPLOAD_PATH');

        foreach ( $groupNames as $groupName ) {
            $subDirNames = scandir($uploadPath . DIRECTORY_SEPARATOR . $groupName);

            foreach ( $subDirNames as $subDirName ) {
                $subDir = $uploadPath . DIRECTORY_SEPARATOR . $groupName . DIRECTORY_SEPARATOR . $subDirName;
                if ( $subDirName === '.' || $subDirName === '..' || ! is_dir($subDir) ) {
                    continue;
                }

                $fileNames = scandir($subDir);

                foreach ( $fileNames as $fileName ) {
                    $savedFile = $subDir . DIRECTORY_SEPARATOR . $fileName;

                    if ( $fileName === '.' || $fileName === '..' || pathinfo($savedFile, PATHINFO_EXTENSION) === 'part' ) {
                        continue;
                    }

                    self::setOneHash(pathinfo($savedFile, PATHINFO_FILENAME), $groupName . DIRECTORY_SEPARATOR . $subDirName . DIRECTORY_SEPARATOR . $fileName);

                }
            }
        }

    }

    /**
     * @param $fileHash
     * @return bool
     */
    public static function deleteOneHash($fileHash)
    {
        $result = false;

        if(class_exists('\Predis\Connection\ConnectionException')) {
            try {
                $result = Redis::hdel(config('aetherupload.REDIS_KEY'), $fileHash);
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }

    public static function deleteAllHashes()
    {
        $result = false;

        if(class_exists('\Predis\Connection\ConnectionException')) {
            try {
                $result = Redis::del(config('aetherupload.REDIS_KEY'));
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }


}