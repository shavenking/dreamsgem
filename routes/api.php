<?php

Route::get('/ok', function () {
    return response()->json();
});

Route::group(['namespace' => 'API'], function () {
    Route::resource('qrcodes', 'QRCodeController', ['only' => ['store', 'update']]);

    Route::resource('users', 'UserController', [
        'only' => ['show', 'store', 'update'],
    ]);

    Route::resource('users.child-accounts', 'ChildAccountController', [
        'only' => ['index', 'store'],
    ])->middleware(['auth:api']);

    Route::resource('dragons', 'DragonController', [
        'only' => ['index', 'update'],
    ])->middleware(['auth:api']);

    Route::resource('users.trees', 'TreeController', [
        'only' => ['index', 'store', 'update'],
    ])->middleware(['auth:api']);

    Route::resource('users.wallets', 'WalletController', [
        'only' => ['index'],
    ])->middleware(['auth:api']);

    Route::resource('users.recalls', 'RecallController', [
        'only' => ['store'],
    ])->middleware(['auth:api']);

    Route::resource('wallets.transfers', 'TransferController', [
        'only' => ['store'],
    ])->middleware(['auth:api']);

    Route::resource('users.operation-histories', 'OperationHistoryController', [
        'only' => ['index']
    ])->middleware(['auth:api']);
});
