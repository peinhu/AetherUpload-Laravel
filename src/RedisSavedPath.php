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

        return $result;
    }

    /**
     * @param $resourceHash
     * @return string
     */
    public static function get($resourceHash)
    {
        $filePath = Redis::hget('aetherupload_resource', $resourceHash);

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
        $result = Redis::hset('aetherupload_resource', $resourceHash, $savedPath);

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

        return $result;
    }

    public static function deleteAll()
    {
        $result = Redis::del('aetherupload_resource');

        return $result;
    }


}