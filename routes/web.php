<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BranchController; // 🔥 ADD THIS
use App\Http\Controllers\CashierController;
use App\Http\Controllers\SalesReportController;


// =====================
// 🔥 CLEAR CACHE
// =====================
Route::get('/clear', function () {
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return 'Cache cleared!';
});


// =====================
// 🔥 FRESH MIGRATE
// =====================
Route::get('/migrate', function () {
    Artisan::call('migrate:fresh', ['--force' => true]);
    return 'Database refreshed + migrated';
});


// =====================
// 🔥 CREATE ADMIN
// =====================
Route::get('/create-user', function () {

    \App\Models\User::truncate();

    \App\Models\User::create([
        'name' => 'Admin',
        'username' => 'admin',
        'email' => 'admin@gmail.com',
        'password' => bcrypt('12345678'),
        'role' => 'admin',
    ]);

    return 'Admin created';
});


// =====================
// 🔥 CREATE BRANCHES
// =====================
Route::get('/create-branches', function () {

    DB::table('branches')->truncate();

    DB::table('branches')->insert([
        ['name' => 'San Isidro'],
        ['name' => 'Arayat'],
        ['name' => 'Mexico'],
        ['name' => 'Capas'],
        ['name' => 'Magalang'],
        ['name' => 'Mabalacat'],
        ['name' => 'Angeles'],
    ]);

    return 'Branches inserted!';
});


// =====================
// AUTH
// =====================
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


// =====================
// ADMIN GROUP
// =====================
Route::prefix('admin')->group(function () {

    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // POS
    Route::get('/pos', function () {
        return view('admin.pos');
    });

    // =====================
    // 🔥 BRANCHES (CONTROLLER VERSION)
    // =====================
    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches/store', [BranchController::class, 'store']);

    // =====================
    // PRODUCTS
    // =====================
    Route::prefix('products')->group(function () {

        Route::get('/', [ProductController::class, 'index']);
        Route::get('/create', [ProductController::class, 'create']);
        Route::post('/', [ProductController::class, 'store']);

        Route::get('/{id}/edit', [ProductController::class, 'edit']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::get('/{id}/delete', [ProductController::class, 'delete']);

        Route::get('/export', [ProductController::class, 'export']);
    });

    // INVENTORY
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/inventory/export', [InventoryController::class, 'export']);
    Route::get('/movements/export', [InventoryController::class, 'exportMovements']);
    Route::post('/transfer', [InventoryController::class, 'transfer']);

    // USERS
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/store', [UserController::class, 'store']);

    // REPORTS
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
// OTHER
// =====================
Route::get('/inventory-dashboard', function () {
    return view('inventory.dashboard');
});