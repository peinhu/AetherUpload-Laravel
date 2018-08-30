<?php

namespace AetherUpload;

class Responser
{

    public static function returnResult($result)
    {
        return response()->json($result);
    }

    public static function reportError($result, $message)
    {
        $result['error'] = $message;

        return response()->json($result);
    }
}