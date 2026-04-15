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
use App\Http\Controllers\CollectionController;


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

// ✅ DAILY SALES MANAGER
Route::get('/manager/daily-sales', [SalesReportController::class, 'daily'])->middleware('auth')->name('manager.daily.sales');

// ✅ SALES REPORT MANAGER
Route::get('/manager/sales-report', [SalesReportController::class, 'perBranch'])->middleware('auth')->name('manager.sales.report');

// ✅ BRANCH STOCK MANAGER
Route::get('/manager/inventory', [ProductController::class, 'index'])->middleware('auth')->name('manager.inventory');

// ✅ INVENTORY REPORT MANAGER
Route::get('/manager/inventory-report', [InventoryController::class, 'overviewStock'])->middleware('auth')->name('manager.inventory.report');

// ✅ ADD STOCK MANAGER
Route::get('/manager/add-stock', [InventoryController::class, 'create'])->middleware('auth')->name('manager.add.stock');
Route::post('/manager/add-stock', [InventoryController::class, 'store'])->middleware('auth')->name('manager.add.stock.store');

// ✅ TRANSFER IN MANAGER
Route::get('/manager/transfer-in', [InventoryController::class, 'transferInAdmin'])
    ->middleware('auth')
    ->name('manager.transfer.in');

// ✅ TRANSFER OUT MANAGER
Route::get('/manager/transfer-out', [InventoryController::class, 'transferOutAdmin'])
    ->middleware('auth')
    ->name('manager.transfer.out');

    // ✅ CHANGE PASSWORD MANAGER
Route::get('/manager/change-password', function () {
    return view('cashier.change-password');
})->middleware('auth');

Route::post('/manager/change-password', [UserController::class, 'changePassword'])
    ->middleware('auth');

    // ✅ REQUEST ACCESS MANAGER
Route::get('/manager/request-access', [CollectionController::class, 'requestAccess'])
    ->middleware('auth')
    ->name('manager.request.access');

Route::post('/manager/request-access/open', [CollectionController::class, 'openTransaction'])
    ->middleware('auth')
    ->name('manager.request.open');

// ✅ MANAGER DEPOSIT
Route::get('/manager/deposit', [CollectionController::class, 'deposit'])
    ->middleware('auth');

Route::post('/manager/deposit', [CollectionController::class, 'depositStore'])
    ->middleware('auth');

// ✅ DEPOSIT EXPORT
Route::get('/cashier/deposit/excel', [CollectionController::class, 'depositExcel'])
    ->middleware('auth')
    ->name('cashier.deposit.excel');

Route::get('/cashier/deposit/pdf', [CollectionController::class, 'depositPdf'])
    ->middleware('auth')
    ->name('cashier.deposit.pdf');

Route::get('/manager/deposit/excel', [CollectionController::class, 'depositExcel'])
    ->middleware('auth')
    ->name('manager.deposit.excel');

Route::get('/manager/deposit/pdf', [CollectionController::class, 'depositPdf'])
    ->middleware('auth')
    ->name('manager.deposit.pdf');


// =====================
// ADMIN GROUP
// =====================
Route::prefix('admin')->group(function () {

    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ✅ SHARED COLLECTION PAGE
    Route::get('/collection', [CollectionController::class, 'today']);

    Route::get('/inventory', [InventoryController::class, 'overviewStock']);
    Route::get('/inventory/export/excel', [InventoryController::class, 'exportExcel']);
    Route::get('/inventory/export/pdf', [InventoryController::class, 'exportPdf']);

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
        Route::post('/update/{id}', [ProductController::class, 'update']);
        Route::get('/{id}/delete', [ProductController::class, 'delete']);

        Route::get('/export', [ProductController::class, 'export']);
    });

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

    Route::get('/sales/brand/pdf', [SalesReportController::class, 'brandPdf'])
        ->name('report.brand.pdf');

    Route::get('/sales/brand/excel', [SalesReportController::class, 'brandExcel'])
        ->name('report.brand.excel');
});


// =====================
// 🔥 CASHIER
// =====================
Route::prefix('cashier')->group(function () {

    Route::get('/', [CashierController::class, 'index'])
        ->name('cashier.dashboard');

    Route::post('/checkout', [CashierController::class, 'checkout']);

    Route::get('/inventory-stock', [InventoryController::class, 'overviewStock'])
        ->name('cashier.inventory.stock');

    Route::get('/collection-receipt', [CollectionController::class, 'create'])
        ->name('cashier.collection.create');

    Route::post('/collection-receipt', [CollectionController::class, 'store'])
        ->name('cashier.collection.store');

    Route::get('/transfer-in', [InventoryController::class, 'transferInForm'])
        ->name('cashier.transfer.in');

    Route::post('/transfer-in', [InventoryController::class, 'transferInStore'])
        ->name('cashier.transfer.in.store');

    Route::get('/incoming', [InventoryController::class, 'incoming']);
    Route::post('/receive/{id}', [InventoryController::class, 'receive']);

    Route::get('/collection-today', [CollectionController::class, 'today'])
        ->name('cashier.collection.today');

    Route::get('/collection/export/pdf', [CollectionController::class, 'exportPdf'])
        ->name('cashier.collection.export.pdf');

    Route::get('/collection/export/excel', [CollectionController::class, 'exportExcel'])
        ->name('cashier.collection.export.excel');

    Route::get('/collection-cancel', [CollectionController::class, 'cancelForm'])
        ->name('cashier.collection.cancel');

    Route::post('/collection-cancel', [CollectionController::class, 'cancelStore'])
        ->name('cashier.collection.cancel.store');

    Route::get('/return-receipt', [CollectionController::class, 'returnForm'])
        ->name('cashier.return.create');

    Route::post('/return-receipt', [CollectionController::class, 'returnStore'])
        ->name('cashier.return.store');

    Route::get('/deposit', [CollectionController::class, 'deposit'])
        ->name('cashier.deposit');

    Route::post('/deposit', [CollectionController::class, 'depositStore'])
        ->name('cashier.deposit.store');

    Route::get('/change-password', [AuthController::class, 'showChangePassword'])
        ->name('cashier.password');

    Route::post('/change-password', [AuthController::class, 'changePassword'])
        ->name('cashier.password.update');
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


// =====================
// RUN LIVE MIGRATION
// =====================
Route::get('/run-migrate', function () {

    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_04_14_120115_create_collections_table.php',
        '--force' => true
    ]);

    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_04_14_120213_create_collection_items_table.php',
        '--force' => true
    ]);

    return nl2br(Artisan::output());
});