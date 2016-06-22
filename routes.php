<?php

if(Config::get('aetherupload.ENABLE_EXAMPLE_PAGE')===true)
{
    Route::get('aetherupload', function () {

        return view('aetherupload::example');
    });

    Route::get('aetherupload/example', function () {

        return view('aetherupload::example');
    });
}

Route::post('aetherupload/init',function(\Peinhu\AetherUpload\Uploader $uploader){

    return $uploader->init();

})->middleware(Config::get('aetherupload.MIDDLEWARE_UPLOAD'));

Route::post('aetherupload/upload',function(\Peinhu\AetherUpload\Uploader $uploader){

    return $uploader->save();

});

Route::get('aetherupload/display/{resourceName}',function(\Peinhu\AetherUpload\Uploader $uploader,$resourceName){

    return $uploader->displayResource($resourceName);

})->middleware(Config::get('aetherupload.MIDDLEWARE_DISPLAY'));

Route::get('aetherupload/download/{resourceName}/{newName}',function(\Peinhu\AetherUpload\Uploader $uploader,$resourceName,$newName){

    return $uploader->downloadResource($resourceName,$newName);

})->middleware(Config::get('aetherupload.MIDDLEWARE_DOWNLOAD'));

