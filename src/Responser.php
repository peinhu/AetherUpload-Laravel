<?php

namespace AetherUpload;

class Responser
{

    public static function returnResult($result)
    {
        return response()->json($result);
    }

    public static function reportError($message)//, $deleteFiles = false, $headerHandler = null, $partialFileHandler = null
    {
//        if ( $deleteFiles ) {
//            @unlink($uploadHead);
//            @unlink($uploadPartialFile);
//        }

        $result = [
            'error' => $message,
        ];

        return response()->json($result);
    }
}