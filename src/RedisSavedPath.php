<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Redis;

class RedisSavedPath
{

    /**
     * @param $key
     * @return bool
     */
    public static function exists($key)
    {
        $result = Redis::hexists('aetherupload_resource', $key);

        if ( $result !== 1 && $result !== 0 ) {
            throw new \Exception('exists error');
        }

        return $result;
    }

    /**
     * @param $key
     * @return string
     */
    public static function get($key)
    {
        $result = Redis::hget('aetherupload_resource', $key);

        if ( $result === null) {
            throw new \Exception('read error');
        }

        return $result;
    }

    /**
     * set or overwrite a hash
     * @param $key
     * @param $savedPath
     * @return bool
     */
    public static function set($key, $savedPath)
    {
        $result = Redis::hset('aetherupload_resource', $key, $savedPath);

        if ( $result !== 0 && $result !== 1 ) {
            throw new \Exception('write error');
        }

        return $result;
    }

    /**
     * @param $keyArr
     * @return bool
     */
    public static function setMulti($keyArr)
    {
        $result = Redis::hmset('aetherupload_resource', $keyArr);

        return $result;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function delete($key)
    {
        $result = Redis::hdel('aetherupload_resource', $key);

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
