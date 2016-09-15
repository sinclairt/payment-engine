<?php

Route::get('/payment/engine/api/v1/item/restore', \Sinclair\PaymentEngine\Controllers\Api\ItemController::class . '@restore');
Route::post('/payment/engine/api/v1/item/filter', \Sinclair\PaymentEngine\Controllers\Api\ItemController::class . '@filter');
Route::resource('/payment/engine/api/v1/item', \Sinclair\PaymentEngine\Controllers\Api\ItemController::class, [ 'except' => [ 'create', 'edit' ], 'parameters' => ['item' => 'transaction_item'] ]);

Route::get('/payment/engine/api/v1/charge/restore', \Sinclair\PaymentEngine\Controllers\Api\ChargeController::class . '@restore');
Route::post('/payment/engine/api/v1/charge/filter', \Sinclair\PaymentEngine\Controllers\Api\ChargeController::class . '@filter');
Route::resource('/payment/engine/api/v1/charge', \Sinclair\PaymentEngine\Controllers\Api\ChargeController::class, [ 'except' => [ 'create', 'edit' ] ]);

Route::get('/payment/engine/api/v1/plan/restore', \Sinclair\PaymentEngine\Controllers\Api\PlanController::class . '@restore');
Route::post('/payment/engine/api/v1/plan/filter', \Sinclair\PaymentEngine\Controllers\Api\PlanController::class . '@filter');
Route::resource('/payment/engine/api/v1/plan', \Sinclair\PaymentEngine\Controllers\Api\PlanController::class, [ 'except' => [ 'create', 'edit' ] ]);

Route::get('/payment/engine/api/v1/transaction/restore', \Sinclair\PaymentEngine\Controllers\Api\TransactionController::class . '@restore');
Route::post('/payment/engine/api/v1/transaction/filter', \Sinclair\PaymentEngine\Controllers\Api\TransactionController::class . '@filter');
Route::resource('/payment/engine/api/v1/transaction', \Sinclair\PaymentEngine\Controllers\Api\TransactionController::class, [ 'except' => [ 'create', 'edit' ] ]);

