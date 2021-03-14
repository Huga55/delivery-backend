<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::get('/query/test', 'query\QueryController@test');


// routes of auth
Route::get('auth/me', 'AuthController@checkUser');
Route::post('auth/register', 'RegisterController@register');
Route::post('auth/login', 'AuthController@authUser');

// query to dadata to get full address
Route::post('/query/address', 'AddressController@getAddress');
Route::post('query/map/address', 'AddressController@fromCoordinates');

// get and update captcha in page feedback
Route::get('/captcha', 'FeedbackController@getCaptcha');

// send message of feedback
Route::post('/feedback', 'FeedbackController@sendMessage');

// create new delivery
Route::post('/delivery/create', 'DeliveryController@create');

Route::get('/register/getName/{inn}', 'RegisterController@getNameOrganization');

Route::post('/delivery/price', 'DeliveryController@getPriceFromDostavista');

// password recovery
Route::post('/remember/send', 'RememberController@sendLink');
Route::post('/remember/password', 'RememberController@sendNewPassword');

Route::post('/dostavista', 'DostavistaController@get');

Route::group(['middleware' => 'auth:api'], function() {
    //Route::post('/delivery/create', 'DeliveryController@create');
    //Route::get('/auth/test', 'AuthController@testQuery'); //test of work of middleware

    Route::post('/book/create', 'lk\BookController@createAddress');
    Route::get('/book/get/{countNeed}/{currentPage}/{nameFilter?}/{newFilter?}', 'lk\BookController@getAddress');
    Route::put('/book/change', 'lk\BookController@changeAddress');
    Route::put('/book/delete', 'lk\BookController@deleteAddress');

    Route::get('/order/lastData', 'DeliveryController@lastData');
    Route::post('/order/get', 'DeliveryController@getOrders');
    Route::get('/order/docs/{id}', 'DeliveryController@getDocsOfOrder');
    Route::post('/order/repeat', 'DeliveryController@repeatOrder');
    //Route::get('/order/lastData', 'DeliveryController@lastData');

    Route::get('/record/get/{countNeed}/{currentPage}/{dateStart}/{dateFinish}/{doc_type}/{type}', 'lk\RecordController@getRecords');

    Route::put('/user/change', 'lk\UserController@changeData');

    Route::post('/user/avatar', 'lk\UserController@changeAvatar');

    Route::put('/user/logout', 'lk\UserController@logout');

    Route::delete('/auth/logout/{id}', 'AuthController@logout');
});

Route::get('admin/auth/check', 'Admin\AuthController@check');
Route::post('admin/auth/login', 'Admin\AuthController@login');

Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
    Route::post('auth/logout', 'Admin\AuthController@logout');
    Route::post('auth/register', 'Admin\AuthController@register');

    Route::post('user/getAll', 'Admin\UserController@getAll');
    Route::get('user/getOne/{id}', 'Admin\UserController@getOne');
    Route::delete('user/delete', 'Admin\UserController@delete');

    Route::post('order/getAll', 'Admin\OrderController@getAll');
    Route::get('order/getOne/{id}', 'Admin\OrderController@getOne');
    Route::post('order/delete', 'Admin\OrderController@delete');
    Route::post('order/file', 'Admin\OrderController@fileCreate');
    Route::post('order/file/delete', 'Admin\OrderController@fileDelete');

    Route::get('page/get', 'Admin\PageController@get');
    Route::post('page/set', 'Admin\PageController@change');
    Route::delete('page/delete', 'Admin\PageController@delete');

});


