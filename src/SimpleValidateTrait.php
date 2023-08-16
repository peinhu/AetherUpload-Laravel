<?php
namespace AetherUpload;

trait SimpleValidateTrait
{

    function validatedWithError($request, $rules)
    {

        foreach ( $rules as $name => $rule ) {
            $item = $request->input($name);
            if ( $rule === "required" ) {
                if ( ! isset($item) || empty($item) ) {
                    return true;
                }
            } else {
                if ( $rule === "present" ) {
                    if ( ! isset($item) ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

}