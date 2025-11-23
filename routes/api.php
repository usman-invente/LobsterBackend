<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegisterController;
use App\Http\Controllers\OffloadRecordController;
use App\Http\Controllers\ReceivingBatchController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\LossAdjustmentController;
use App\Http\Controllers\CrateController;
use App\Http\Controllers\LooseStockController;
use App\Http\Controllers\TankController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportsController;

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->get('/user', [RegisterController::class, 'me']);
Route::middleware('auth:sanctum')->post('/logout', [RegisterController::class, 'logout']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('offload-records', OffloadRecordController::class);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users', [UserController::class, 'store']); // Create user
    Route::get('/users/{user}', [UserController::class, 'show']); // Get user (with permissions)
    Route::put('/users/{user}', [UserController::class, 'update']); // Update user/permissions
    Route::get('/sidebar-menus', [UserController::class, 'sidebarMenus']); // List all possible menus
    Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);
    Route::get('/reports/stock-by-tanks', [ReportsController::class, 'stockByTanks']);
    Route::get('/reports/stock-by-size', [ReportsController::class, 'stockBySize']);
    Route::get('/reports/stock-by-boat', [ReportsController::class, 'stockByBoat']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Receiving Batches
    Route::apiResource('receiving-batches', ReceivingBatchController::class);
    Route::get('/crates', [CrateController::class, 'index']);
    Route::put('/crates/{id}', [CrateController::class, 'update']);
    Route::get('/tanks', [TankController::class, 'index']);
    // 3. Create loose stock
    Route::post('/loose-stock', [LooseStockController::class, 'store']);
    // Crates
    // Route::get('crates/available', [CrateController::class, 'available']);
    // Route::get('crates/{crateNumber}', [CrateController::class, 'showByNumber']);
    // Route::put('crates/{crate}', [CrateController::class, 'update']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard-stats', [DashboardController::class, 'stats']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Dispatches

    Route::apiResource('dispatches', DispatchController::class);
    Route::get('dispatches/summary', [DispatchController::class, 'summary']);
    Route::get('dispatches/available-stock', [DispatchController::class, 'availableStock']);
});


Route::middleware('auth:sanctum')->group(function () {
    // Loss Adjustments CRUD
    Route::apiResource('loss-adjustments', LossAdjustmentController::class);
    Route::delete('/loss-adjustments/{id}', [LossAdjustmentController::class, 'destroy']);
    Route::put('/loss-adjustments/{id}', [LossAdjustmentController::class, 'update']);
    Route::get('/tanks/{tank}/crates', [TankController::class, 'crates']);
    // Loss Reports
    Route::get('loss-adjustments-summary', [LossAdjustmentController::class, 'summary']);
    Route::get('loss-adjustments-trends', [LossAdjustmentController::class, 'trends']);
    Route::get('loss-adjustments-by-tank/{tankNumber}', [LossAdjustmentController::class, 'byTank']);


});



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
