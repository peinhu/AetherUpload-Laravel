<?php

namespace Peinhu\AetherUpload;

class Responser
{

    public static function returnResult($result)
    {
        return response()->json($result);
    }

    public static function reportError($message, $deleteFiles = false, $uploadHead = '', $uploadFilePartial = '')
    {
        if ( $deleteFiles ) {
            @unlink($uploadHead);
            @unlink($uploadFilePartial);
        }

        $result = [
            'error' => '错误：' . $message,
        ];

        return response()->json($result);
    }
}