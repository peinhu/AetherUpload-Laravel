<?php
namespace Peinhu\AetherUpload;

abstract class BaseUploader{

    abstract public function save();

    abstract public function displayResource($resourceName);

    abstract public function downloadResource($resourceName,$newName);

    abstract public function cleanUpDir();

    abstract protected function reportError($message = "");

    abstract protected function returnResult();

}