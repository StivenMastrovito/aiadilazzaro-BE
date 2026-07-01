<?php

use App\Http\Controllers\Admin\categoryController;
use App\Http\Controllers\Admin\ordersController;
use App\Http\Controllers\Admin\productsController;
use App\Http\Controllers\Admin\QzController;
use App\Http\Controllers\Admin\tablesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/products')->controller(productsController::class)->group(function () {
    Route::get('', 'index');
    Route::get('/{product}', 'show');
    Route::post('', 'store');
    Route::put('/{product}', 'update');
    Route::delete('/{product}', 'destroy');
});

Route::prefix('/categories')->controller(categoryController::class)->group(function () {
    Route::get('', 'index');
    Route::post('', 'store');
    Route::put('/{category}', 'update');
    Route::delete('/{category}', 'destroy');
});

Route::prefix('/tables')->controller(tablesController::class)->group(function () {
    Route::get('', 'index');
    Route::post('', 'store');
    Route::put('/{table}', 'update');
    Route::delete('/{table}', 'destroy');
});

Route::prefix('/orders')->controller(ordersController::class)->group(function () {
    Route::get('', 'index');
    Route::get('/{order}', 'show');
    Route::post('', 'store');
    Route::put('/{order}', 'update');
    Route::delete('/{order}', 'destroy');
    Route::post('/products/{order}', 'removeProducts');
    Route::post('/note/{order}', 'removeProductsWithNote');
    Route::post('/close/{order}', 'closeOrder');
    Route::post('/getUnprinted/{order}', 'getUnprinted');
    Route::post('/printed', 'updatePrinted');

});


Route::get('/qz-certificate', [QzController::class, 'certificate']);
Route::post('/qz-sign', [QzController::class, 'sign']);

// Route::post('/print', [PrintController::class, 'print']);
