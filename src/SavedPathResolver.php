<?php

namespace AetherUpload;

class SavedPathResolver
{

    public static function encode($group, $groupSubDir, $name)
    {
        return $group . '_' . $groupSubDir . '_' . $name;
    }

    public static function decode($savedPath)
    {
        list($group, $groupSubDir, $name) = explode('_', $savedPath);

        return (object)[
            'group'        => $group,
            'groupSubDir'  => $groupSubDir,
            'resourceName' => $name,
        ];
    }

}