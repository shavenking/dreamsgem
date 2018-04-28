<?php

Route::group(['namespace' => 'API'], function () {
    Route::resource('users', 'UserController', [
        'only' => ['store'],
    ]);

    Route::resource('users.child-accounts', 'ChildAccountController', [
        'only' => ['store'],
    ])->middleware(['auth:api', 'scopes:create-child-accounts']);

    Route::resource('users.dragons', 'DragonController', [
        'only' => ['store'],
    ])->middleware(['auth:api', 'scopes:create-dragons']);

    Route::resource('users.trees', 'TreeController', [
        'only' => ['store'],
    ])->middleware(['auth:api', 'scopes:create-trees']);
});
