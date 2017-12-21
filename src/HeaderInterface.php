<?php

namespace AetherUpload;

interface HeaderInterface {

    public function create($name);

    public function write($name, $content);

    public function read($name);

    public function delete($name);

}