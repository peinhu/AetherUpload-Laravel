<?php

namespace Peinhu\AetherUpload;

class Responser
{

    public static function returnResult($result)
    {
        return response()->json($result);
    }

    public static function reportError($message, $deleteFiles = false, $uploadHead = '', $uploadPartialFile = '')
    {
        if ( $deleteFiles ) {
            @unlink($uploadHead);
            @unlink($uploadPartialFile);
        }

        $result = [
            'error' => '错误：' . $message,
        ];

        return response()->json($result);
    }
}