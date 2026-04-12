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
// 🔥 FIX DB
// =====================
Route::get('/fix-db', function () {

    $constraints = DB::select("
        SELECT conname 
        FROM pg_constraint 
        WHERE conrelid = 'stock_movements'::regclass
        AND contype = 'c'
    ");

    foreach ($constraints as $c) {
        DB::statement("ALTER TABLE stock_movements DROP CONSTRAINT {$c->conname}");
    }

    DB::statement("
        ALTER TABLE stock_movements 
        ADD CONSTRAINT stock_movements_type_check 
        CHECK (type IN ('IN','OUT','IN_REQUEST'))
    ");

    return 'DB FIXED FINAL';
});


// =====================
// 🔥 ADD COLUMN
// =====================
Route::get('/fix-column', function () {

    DB::statement("
        ALTER TABLE stock_movements 
        ADD COLUMN IF NOT EXISTS from_branch_id BIGINT
    ");

    return 'COLUMN ADDED';
});


// =====================
// AUTH
// =====================
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


// =====================
// 🔥 MANAGER
// =====================
Route::get('/manager', [InventoryController::class, 'managerDashboard'])->middleware('auth');
Route::get('/manager/approvals', [InventoryController::class, 'approvals'])->middleware('auth');

Route::get('/manager/transfer-out', [InventoryController::class, 'transferOutManager'])->middleware('auth');
Route::post('/manager/release/{id}', [InventoryController::class, 'release'])->middleware('auth');


// =====================
// ADMIN GROUP
// =====================
Route::prefix('admin')->group(function () {

    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/pos', function () {
        return view('admin.pos');
    });

    Route::get('/branches', [BranchController::class, 'index']);
    Route::post('/branches/store', [BranchController::class, 'store']);
    Route::post('/branches/update/{id}', [BranchController::class, 'update']);
    Route::post('/branches/delete/{id}', [BranchController::class, 'delete']);

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

    Route::get('/inventory/add-stock', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/add-stock', [InventoryController::class, 'store'])->name('inventory.store');

    Route::get('/inventory/transfer-out', [InventoryController::class, 'transferOutAdmin']);
    Route::get('/inventory/transfer-in', [InventoryController::class, 'transferInAdmin']);

    Route::post('/inventory/transfer-out', [InventoryController::class, 'transferOutStore']);
    Route::post('/inventory/transfer-accept/{id}', [InventoryController::class, 'acceptTransfer']);

    Route::post('/inventory/transfer-in-old', [InventoryController::class, 'transferInStore']);

    Route::post('/manager/approve/{id}', [InventoryController::class, 'approve']);
    Route::post('/manager/reject/{id}', [InventoryController::class, 'reject']);

    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users/store', [UserController::class, 'store']);
    Route::get('/manage', [UserController::class, 'manage']);
    Route::post('/users/update/{id}', [UserController::class, 'update']);
    Route::post('/users/delete/{id}', [UserController::class, 'delete']);

    Route::get('/reports', function () {
        return view('admin.reports');
    });

    Route::get('/sales/daily', [SalesReportController::class, 'daily']);
    Route::get('/sales/daily/pdf', [SalesReportController::class, 'exportDailyPdf']);
    Route::get('/sales/daily/excel', [SalesReportController::class, 'exportExcel']);

    Route::get('/sales/branch', [SalesReportController::class, 'perBranch']);
    Route::get('/sales/branch/data', [SalesReportController::class, 'branchData']);
    Route::get('/sales/branch/pdf', [SalesReportController::class, 'exportPdf']);
    Route::get('/sales/branch/excel', [SalesReportController::class, 'exportBranchExcel']);

    Route::get('/sales/brand', [SalesReportController::class, 'perBrand']);

    // 🔥 FIXED HERE
    Route::get('/sales/brand/pdf', [SalesReportController::class, 'brandPdf'])
        ->name('report.brand.pdf');

    Route::get('/sales/brand/excel', [SalesReportController::class, 'brandExcel'])
        ->name('report.brand.excel');
});


// =====================
// 🔥 CASHIER
// =====================
Route::prefix('cashier')->group(function () {

    Route::get('/', [CashierController::class, 'index']);
    Route::post('/checkout', [CashierController::class, 'checkout']);

    Route::get('/transfer-in', [InventoryController::class, 'transferInForm'])
        ->name('cashier.transfer.in');

    Route::post('/transfer-in', [InventoryController::class, 'transferInStore'])
        ->name('cashier.transfer.in.store');

    Route::get('/incoming', [InventoryController::class, 'incoming']);
    Route::post('/receive/{id}', [InventoryController::class, 'receive']);
});


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