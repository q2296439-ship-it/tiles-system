<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\SalesReportController;


// =====================
// AUTH
// =====================
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

// 🔥 LOGOUT
Route::post('/logout', [AuthController::class, 'logout']);


// =====================
// ADMIN GROUP 🔥
// =====================
Route::prefix('admin')->group(function () {

    // =====================
    // DASHBOARD
    // =====================
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // =====================
    // POS
    // =====================
    Route::get('/pos', function () {
        return view('admin.pos');
    });

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

    // =====================
    // INVENTORY 🔥
    // =====================
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/inventory/export', [InventoryController::class, 'export']);

    Route::get('/movements/export', [InventoryController::class, 'exportMovements']);
    Route::post('/transfer', [InventoryController::class, 'transfer']);

    // =====================
    // USERS 🔥
    // =====================
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/store', [UserController::class, 'store']);

    // =====================
    // REPORTS
    // =====================
    Route::get('/reports', function () {
        return view('admin.reports');
    });

    // =====================
    // SALES REPORTS 🔥
    // =====================
    Route::get('/sales/daily', [SalesReportController::class, 'daily']);
    Route::get('/sales/branch', [SalesReportController::class, 'perBranch']);

    // 🔥 AUTO UPDATE API (IMPORTANT)
    Route::get('/sales/branch/data', [SalesReportController::class, 'branchData']);

    // =====================
    // PDF EXPORT 🔥
    // =====================
    Route::get('/sales/daily/pdf', [SalesReportController::class, 'exportDailyPdf']);
    Route::get('/sales/branch/pdf', [SalesReportController::class, 'exportPdf']);

    // =====================
    // EXCEL EXPORT 🔥
    // =====================
    Route::get('/sales/daily/excel', [SalesReportController::class, 'exportExcel']);

    // 🔥 FIX: BRANCH EXCEL (ETO YUNG KULANG MO)
    Route::get('/sales/branch/excel', [SalesReportController::class, 'exportBranchExcel']);
});


// =====================
// CASHIER 🔥
// =====================
Route::get('/cashier', [CashierController::class, 'index']);
Route::post('/cashier/checkout', [CashierController::class, 'checkout']);


// =====================
// OTHER DASHBOARDS
// =====================
Route::get('/inventory-dashboard', function () {
    return view('inventory.dashboard');
});