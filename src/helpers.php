<?php

function aetherupload_display_link($savedPath)
{
    return \AetherUpload\Util::getDisplayLink($savedPath);
}

function aetherupload_download_link($savedPath, $newName)
{
    return \AetherUpload\Util::getDownloadLink($savedPath, $newName);
}

if ( ! function_exists('storage_host_field') ) {
    function storage_host_field()
    {
        return \AetherUpload\Util::getStorageHostField();
    }
}

/**
 * 获取resource
 * @param string $name 文件名字
 * @param string $group 文件分组 对应config/aetherupload.php 下的group
 * @return AetherUpload\Resource
 */
function aetherupload_resource($name, $group = 'file')
{
    $subGroup = \AetherUpload\Util::generateSubDirName();
    $resource = new \AetherUpload\Resource($group, \AetherUpload\ConfigMapper::get('group_dir'), $subGroup, $name);
    return $resource;
}
