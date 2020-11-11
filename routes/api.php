<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::group(['prefix' => 'v1'], function () {
    Route::get('init', 'InitController@index')->middleware('api_token');

    Route::post('pin-api/request', 'SubscriptionReferenceController@otpRequest')->middleware('api_token');
    Route::post('pin-api/verify', 'SubscriptionReferenceController@otpVerify')->middleware('api_token');

    Route::get('subscription/{contact}', 'UserSubscriptionController@show');

    Route::get('subscribe', 'SubscriptionController@subscribe')->middleware('api_token');
    Route::get('unsubscribe', 'SubscriptionController@unsubscribe')->middleware('api_token');
});
