<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\SalesReportController;


// =====================
// 🔥 RUN MIGRATIONS (IMPORTANT)
// =====================
Route::get('/migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return 'Migrations completed';
});


// =====================
// 🔥 TEMP CREATE / RESET USER
// =====================
Route::get('/create-user', function () {
    $user = \App\Models\User::where('email', 'admin@gmail.com')->first();

    if ($user) {
        $user->password = bcrypt('12345678');
        $user->role = 'admin';
        $user->save();
    } else {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
        ]);
    }

    return 'User created / reset';
});


// =====================
// AUTH
// =====================
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

// LOGOUT
Route::post('/logout', [AuthController::class, 'logout']);


// =====================
// ADMIN GROUP
// =====================
Route::prefix('admin')->group(function () {

    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/pos', function () {
        return view('admin.pos');
    });

    Route::prefix('products')->group(function () {

        Route::get('/', [ProductController::class, 'index']);
        Route::get('/create', [ProductController::class, 'create']);
        Route::post('/', [ProductController::class, 'store']);

        Route::get('/{id}/edit', [ProductController::class, 'edit']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::get('/{id}/delete', [ProductController::class, 'delete']);

        Route::get('/export', [ProductController::class, 'export']);
    });

    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/inventory/export', [InventoryController::class, 'export']);

    Route::get('/movements/export', [InventoryController::class, 'exportMovements']);
    Route::post('/transfer', [InventoryController::class, 'transfer']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/store', [UserController::class, 'store']);

    Route::get('/reports', function () {
        return view('admin.reports');
    });

    Route::get('/sales/daily', [SalesReportController::class, 'daily']);
    Route::get('/sales/branch', [SalesReportController::class, 'perBranch']);

    Route::get('/sales/branch/data', [SalesReportController::class, 'branchData']);

    Route::get('/sales/daily/pdf', [SalesReportController::class, 'exportDailyPdf']);
    Route::get('/sales/branch/pdf', [SalesReportController::class, 'exportPdf']);

    Route::get('/sales/daily/excel', [SalesReportController::class, 'exportExcel']);
    Route::get('/sales/branch/excel', [SalesReportController::class, 'exportBranchExcel']);
});


// =====================
// CASHIER
// =====================
Route::get('/cashier', [CashierController::class, 'index']);
Route::post('/cashier/checkout', [CashierController::class, 'checkout']);


// =====================
// OTHER DASHBOARDS
// =====================
Route::get('/inventory-dashboard', function () {
    return view('inventory.dashboard');
});