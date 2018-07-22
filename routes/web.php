<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
})->name('index')->middleware('maintenance');

Route::get('email-verifications/{token}', 'EmailVerificationController@update')->name('email-verifications.update')->middleware('maintenance');

Route::get('downloads/android-apk', 'DownloadController@androidAPK')->name('downloads.android-apk')->middleware('maintenance');

Route::prefix('admin')->namespace('Admin')->name('admin.')->group(function () {
    Route::get('login', 'AuthController@getLogin')->name('auth.get-login');
    Route::post('login', 'AuthController@postLogin')->name('auth.post-login');
    Route::get('logout', 'AuthController@logout')->name('auth.logout');

    Route::middleware('auth:admins')->group(function () {
        Route::get('/', 'HomeController@home')->name('home');
    });
});
