<?php

Route::group(['namespace' => 'API'], function () {
    Route::resource('users', 'UserController', [
        'only' => ['show', 'store', 'update'],
    ]);

    // middleware in controller constructor
    Route::resource('users.child-accounts', 'ChildAccountController', [
        'only' => ['index', 'store'],
    ]);

    Route::resource('users.dragons', 'DragonController', [
        'only' => ['store', 'update'],
    ])->middleware(['auth:api']);

    Route::resource('users.trees', 'TreeController', [
        'only' => ['store', 'update'],
    ])->middleware(['auth:api']);
});
