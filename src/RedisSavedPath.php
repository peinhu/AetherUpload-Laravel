<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Redis;
use Predis\Connection\ConnectionException;

class RedisSavedPath
{
    /**
     * @param $resourceHash
     * @return bool
     */
    public static function exists($resourceHash)
    {
        $result = false;

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $result = Redis::hexists('aetherupload_resource', $resourceHash);
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }

    /**
     * @param $resourceHash
     * @return string
     */
    public static function get($resourceHash)
    {
        $filePath = "";

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $filePath = Redis::hget('aetherupload_resource', $resourceHash);
            } catch ( ConnectionException $e ) {

            }
        }

        return $filePath;
    }

    /**
     * set or overwrite a hash
     * @param $resourceHash
     * @param $savedPath
     * @return bool
     */
    public static function set($resourceHash, $savedPath)
    {
        $result = false;

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $result = Redis::hset('aetherupload_resource', $resourceHash, $savedPath);
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }

    /**
     * @param $resourceHashArr
     * @return bool
     */
    public static function setMulti($resourceHashArr)
    {
        $result = false;

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $result = Redis::hmset('aetherupload_resource', $resourceHashArr);
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }

    /**
     * @param $resourceHash
     * @return bool
     */
    public static function delete($resourceHash)
    {
        $result = false;

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $result = Redis::hdel('aetherupload_resource', $resourceHash);
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }

    public static function deleteAll()
    {
        $result = false;

        if ( class_exists('\Predis\Connection\ConnectionException') ) {
            try {
                $result = Redis::del('aetherupload_resource');
            } catch ( ConnectionException $e ) {

            }
        }

        return $result;
    }


}