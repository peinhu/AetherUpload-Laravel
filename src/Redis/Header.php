<?php

namespace AetherUpload\Redis;

use AetherUpload\HeaderInterface;
use Illuminate\Support\Facades\Redis;
use AetherUpload\ConfigMapper;
use Predis\Connection\ConnectionException;

class Header implements HeaderInterface
{

    /**
     * @param $name
     * @return mixed
     */
    public function create($name)
    {
        $result = Redis::hset(ConfigMapper::get('REDIS_HEADER_KEY'), $name, "");

        return $result;
    }

    /**
     * @param $name
     * @param $content
     * @return mixed
     */
    public function write($name, $content)
    {
        $result = Redis::hset(ConfigMapper::get('REDIS_HEADER_KEY'), $name, $content);

        return $result;
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

        return $result;
    }

}