<?php

namespace AetherUpload;

use League\Flysystem\Config;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;

class RedisAdapter extends AbstractAdapter
{
    use NotSupportingVisibilityTrait;

    protected $client;

    public function __construct(RedisClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, Config $config)
    {
        try {
            $this->client->write(basename($path), $contents);
        } catch ( \Exception $e ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newPath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        try {
            $this->client->delete(basename($path));
        } catch ( \Exception $e ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, Config $config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {

        try {
            $content = $this->client->read(basename($path));
        } catch ( \Exception $e ) {
            return false;
        }

        $result['contents'] = $content;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        try {
            $result = $this->client->listContents($directory);
        } catch ( \Exception $e ) {
            return [];
        }

        return array_filter($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function applyPathPrefix($path)
    {
        return false;
    }

    public function exists($path)
    {
        try {
            $result = $this->client->exists(basename($path));
        } catch ( \Exception $e ) {
            return false;
        }

        if ( $result !== 1 ) {
            return false;
        }

        return true;
    }

    public function getClient()
    {
        return $this->client;
    }


}