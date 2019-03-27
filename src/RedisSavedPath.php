<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Redis;

class RedisSavedPath
{

    /**
     * @param $resourceHash
     * @return bool
     */
    public static function exists($resourceHash)
    {
        $result = Redis::hexists('aetherupload_resource', $resourceHash);

        if ( $result !== 1 && $result !== 0 ) {
            throw new \Exception('exists error');
        }

        return $result;
    }

    /**
     * @param $resourceHash
     * @return string
     */
    public static function get($resourceHash)
    {
        $result = Redis::hget('aetherupload_resource', $resourceHash);

        if ( $result === null) {
            throw new \Exception('read error');
        }

        return $result;
    }

    /**
     * set or overwrite a hash
     * @param $resourceHash
     * @param $savedPath
     * @return bool
     */
    public static function set($savedPathKey, $savedPath)
    {
        $result = Redis::hset('aetherupload_resource', $savedPathKey, $savedPath);

        if ( $result !== 0 && $result !== 1 ) {
            throw new \Exception('write error');
        }

        return $result;
    }

    /**
     * @param $resourceHashArr
     * @return bool
     */
    public static function setMulti($resourceHashArr)
    {
        $result = Redis::hmset('aetherupload_resource', $resourceHashArr);

        return $result;
    }

    /**
     * @param $resourceHash
     * @return bool
     */
    public static function delete($resourceHash)
    {
        $result = Redis::hdel('aetherupload_resource', $resourceHash);

        if ( $result !== 1 ) {
            throw new \Exception('delete error');
        }

        return $result;
    }

    public static function deleteAll()
    {
        $result = Redis::del('aetherupload_resource');

        return $result;
    }


}