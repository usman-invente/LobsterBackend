<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\OffloadRecordController;
use App\Http\Controllers\ReceivingBatchController;


Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('offload-records', OffloadRecordController::class);
});


Route::middleware('auth:sanctum')->group(function () {
    // Receiving Batches
    Route::apiResource('receiving-batches', ReceivingBatchController::class);
    
    // Crates
    // Route::get('crates/available', [CrateController::class, 'available']);
    // Route::get('crates/{crateNumber}', [CrateController::class, 'showByNumber']);
    // Route::put('crates/{crate}', [CrateController::class, 'update']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Dispatches
    Route::apiResource('dispatches', DispatchController::class);
    Route::get('dispatches/summary', [DispatchController::class, 'summary']);
    Route::get('dispatches/available-stock', [DispatchController::class, 'availableStock']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
