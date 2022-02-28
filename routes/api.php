<?php

use Illuminate\Http\Request;


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});



Route::group(['prefix'=> '/v1'], function() {


    Route::resource('meeting', 'meetingController', [
        'except' => ['edit', 'create']]);
    Route::resource('meeting/registration', 'registrationController', [
        'only' => ['store', 'destroy']]);

    Route::post('user', 'AuthController@store');

    Route::post('user/signin','AuthController@signin');


});




