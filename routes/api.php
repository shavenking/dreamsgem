<?php

Route::group(['namespace' => 'API'], function () {
    Route::resource('users', 'UserController', [
        'only' => ['show', 'store', 'update'],
    ]);

    Route::resource('users.child-accounts', 'ChildAccountController', [
        'only' => ['index', 'store'],
    ])->middleware(['auth:api']);

    Route::resource('users.dragons', 'DragonController', [
        'only' => ['store', 'update'],
    ])->middleware(['auth:api']);

    Route::resource('users.trees', 'TreeController', [
        'only' => ['index', 'store', 'update'],
    ])->middleware(['auth:api']);

    Route::resource('users.wallets', 'WalletController', [
        'only' => ['index'],
    ])->middleware(['auth:api']);
});
