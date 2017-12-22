<?php

namespace AetherUpload;

use Illuminate\Support\Facades\Redis;
use Predis\Connection\ConnectionException;

class RedisHeader implements HeaderInterface
{

    /**
     * @param $name
     * @return mixed
     */
    public function create($name)
    {
        $result = Redis::hset(ConfigMapper::get('REDIS_HEADER_KEY'), $name, "");

        return true;
    }

    /**
     * @param $name
     * @param $content
     * @return mixed
     */
    public function write($name, $content)
    {
        $result = Redis::hset(ConfigMapper::get('REDIS_HEADER_KEY'), $name, $content);

        return true;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function read($name)
    {
        $content = Redis::hget(ConfigMapper::get('REDIS_HEADER_KEY'), $name);

        return $content;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function delete($name)
    {
        $result = Redis::hdel(ConfigMapper::get('REDIS_HEADER_KEY'), $name);

        return true;
    }

}