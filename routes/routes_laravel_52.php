<?php

if(Config::get('aetherupload.ENABLE_EXAMPLE_PAGE')===true)
{
    Route::get('aetherupload', function () {

        return view('aetherupload::example');
    })->middleware('web');

    Route::get('aetherupload/example', function () {

        return view('aetherupload::example');
    })->middleware('web');

    Route::get('aetherupload/example_source', function () {

        return '<pre>'.htmlspecialchars(File::get(__DIR__.'/../views/example.blade.php'));
    });
}

Route::post('aetherupload/init',function(\Peinhu\AetherUpload\Uploader $uploader){

    return $uploader->init();

})->middleware('web')->middleware(Config::get('aetherupload.MIDDLEWARE_UPLOAD'));

Route::post('aetherupload/upload',function(\Peinhu\AetherUpload\Uploader $uploader){

    return $uploader->save();

})->middleware('web');

Route::get('aetherupload/display/{resourceName}',function(\Peinhu\AetherUpload\Uploader $uploader,$resourceName){

    return $uploader->displayResource($resourceName);

})->middleware('web')->middleware(Config::get('aetherupload.MIDDLEWARE_DISPLAY'));

Route::get('aetherupload/download/{resourceName}/{newName}',function(\Peinhu\AetherUpload\Uploader $uploader,$resourceName,$newName){

    return $uploader->downloadResource($resourceName,$newName);

})->middleware('web')->middleware(Config::get('aetherupload.MIDDLEWARE_DOWNLOAD'));

