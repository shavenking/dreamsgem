<?php

Route::group(['namespace' => 'API'], function () {
    Route::resource('/users', 'UserController', [
        'only' => ['store'],
    ]);
});
