<?php

Route::get('503', function () {
    abort(503);
});

Route::get('/ok', function () {
    return response()->json();
});

Route::post('password-resets', 'API\PasswordResetController@store')->middleware(['throttle:60,1', 'maintenance']);

Route::group(['namespace' => 'API', 'middleware' => ['maintenance']], function () {
    Route::resource('qrcodes', 'QRCodeController', ['only' => ['store', 'update']]);

    Route::resource('users', 'UserController', [
        'only' => ['show', 'store', 'update'],
    ]);

    Route::get('users/{user}/available-tree-types', 'UserController@availableTreeTypes');

    Route::resource('users.tree-stats', 'TreeStatsController', [
        'only' => ['index'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::resource('users.child-accounts', 'ChildAccountController', [
        'only' => ['index', 'store'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::resource('dragons', 'DragonController', [
        'only' => ['index', 'store', 'update'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::resource('users.trees', 'TreeController', [
        'only' => ['index', 'store', 'update'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::resource('users.wallets', 'WalletController', [
        'only' => ['index', 'update'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::resource('users.recalls', 'RecallController', [
        'only' => ['store'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::resource('wallets.transfers', 'TransferController', [
        'only' => ['store'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::resource('gems.wallet-transfer-applications', 'WalletTransferApplicationController', [
        'only' => ['index', 'store'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::get('wallet-transfer-rate', 'WalletTransferRateController@index');

    Route::get('wallet-transfer-map', 'WalletTransferMapController@index');

    Route::resource('users.operation-histories', 'OperationHistoryController', [
        'only' => ['index']
    ])->middleware(['auth:api', 'email.verified']);

    Route::get('users/{user}/dragon-summary', 'DragonSummaryController@index')->middleware(['auth:api', 'email.verified']);
    Route::get('users/{user}/tree-summary', 'TreeSummaryController@index')->middleware(['auth:api', 'email.verified']);
    Route::get('dragon-prices', 'DragonPriceController@index')->middleware(['auth:api', 'email.verified']);
    Route::get('tree-prices', 'TreePriceController@index')->middleware(['auth:api', 'email.verified']);

    Route::resource('card-applications', 'CardApplicationController', [
        'only' => ['index', 'store'],
    ])->middleware(['auth:api', 'email.verified']);

    Route::get('dragon-activation-candidates', 'DragonActivationCandidateController@index')->middleware(['auth:api', 'email.verified']);
    Route::get('tree-activation-candidates', 'TreeActivationCandidateController@index')->middleware(['auth:api', 'email.verified']);
});
