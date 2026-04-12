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
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\SalesReportController;


// =====================
// 🔥 CLEAR CACHE
// =====================
Route::get('/clear', function () {
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
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
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


// =====================
// MANAGER DASHBOARD
// =====================
Route::get('/manager', [InventoryController::class, 'approvals'])->middleware('auth');


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
    // BRANCHES
    // =====================
    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches/store', [BranchController::class, 'store']);

    // 🔥 FIX (ITO KULANG MO)
    Route::post('/branches/update/{id}', [BranchController::class, 'update']);
    Route::post('/branches/delete/{id}', [BranchController::class, 'delete']);

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
    // INVENTORY
    // =====================
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::get('/inventory/export', [InventoryController::class, 'export']);
    Route::get('/movements/export', [InventoryController::class, 'exportMovements']);
    Route::post('/transfer', [InventoryController::class, 'transfer']);

    Route::get('/inventory/add-stock', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/add-stock', [InventoryController::class, 'store'])->name('inventory.store');

    Route::get('/inventory/transfer-in', [InventoryController::class, 'transferInForm'])->name('inventory.transfer.in');
    Route::post('/inventory/transfer-in', [InventoryController::class, 'transferInStore'])->name('inventory.transfer.store');

    // =====================
    // MANAGER APPROVAL ACTIONS
    // =====================
    Route::post('/manager/approve/{id}', [InventoryController::class, 'approve']);
    Route::post('/manager/reject/{id}', [InventoryController::class, 'reject']);

    // =====================
    // USERS
    // =====================
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/store', [UserController::class, 'store']);

    // 🔥 NEW: MANAGE ACCOUNT
    Route::get('/manage', [UserController::class, 'manage']);

    // 🔥 USER UPDATE & DELETE
    Route::post('/users/update/{id}', [UserController::class, 'update']);
    Route::post('/users/delete/{id}', [UserController::class, 'delete']);

    // =====================
    // REPORTS
    // =====================
    Route::get('/reports', function () {
        return view('admin.reports');
    });

    Route::get('/sales/daily', [SalesReportController::class, 'daily'])->name('report.daily');
    Route::get('/sales/daily/pdf', [SalesReportController::class, 'exportDailyPdf'])->name('report.daily.pdf');
    Route::get('/sales/daily/excel', [SalesReportController::class, 'exportExcel'])->name('report.daily.excel');

    Route::get('/sales/branch', [SalesReportController::class, 'perBranch'])->name('report.branch');
    Route::get('/sales/branch/data', [SalesReportController::class, 'branchData']);
    Route::get('/sales/branch/pdf', [SalesReportController::class, 'exportPdf'])->name('report.branch.pdf');
    Route::get('/sales/branch/excel', [SalesReportController::class, 'exportBranchExcel'])->name('report.branch.excel');

    Route::get('/sales/brand', [SalesReportController::class, 'perBrand'])->name('report.brand');
    Route::get('/sales/brand/pdf', [SalesReportController::class, 'brandPdf'])->name('report.brand.pdf');
    Route::get('/sales/brand/excel', [SalesReportController::class, 'brandExcel'])->name('report.brand.excel');
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


// =====================
// FIX MANAGER PASSWORD
// =====================
Route::get('/fix-manager-pass', function () {

    $user = \App\Models\User::where('email', 'manager@gmail.com')->first();

    if ($user) {
        $user->password = bcrypt('12345678');
        $user->save();
    }

    return 'Manager password fixed!';
});