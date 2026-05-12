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
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CashTransferController;
use App\Http\Controllers\AnnouncementController;


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
Route::post('/manager/release/{id}', [InventoryController::class, 'release'])->middleware('auth');

Route::get('/manager/daily-sales', [SalesReportController::class, 'daily'])->middleware('auth')->name('manager.daily.sales');
Route::get('/manager/sales-report', [SalesReportController::class, 'perBranch'])->middleware('auth')->name('manager.sales.report');
Route::get('/manager/inventory', [ProductController::class, 'index'])->middleware('auth')->name('manager.inventory');
Route::get('/manager/inventory-report', [InventoryController::class, 'overviewStock'])->middleware('auth')->name('manager.inventory.report');

Route::get('/manager/add-stock', [InventoryController::class, 'create'])->middleware('auth')->name('manager.add.stock');
Route::post('/manager/add-stock', [InventoryController::class, 'store'])->middleware('auth')->name('manager.add.stock.store');

Route::get('/manager/transfer-in', [InventoryController::class, 'transferInAdmin'])->middleware('auth')->name('manager.transfer.in');
Route::get('/manager/transfer-out', [InventoryController::class, 'transferOutAdmin'])->middleware('auth')->name('manager.transfer.out');

Route::get('/manager/change-password', function () {
    return view('cashier.change-password');
})->middleware('auth');

Route::post('/manager/change-password', [UserController::class, 'changePassword'])->middleware('auth');

Route::get('/manager/request-access', [CollectionController::class, 'requestAccess'])
    ->middleware('auth')
    ->name('manager.request.access');

Route::post('/manager/request-access/open', [CollectionController::class, 'openTransaction'])
    ->middleware('auth')
    ->name('manager.request.open');

Route::get('/manager/deposit', [CollectionController::class, 'deposit'])->middleware('auth');
Route::post('/manager/deposit', [CollectionController::class, 'depositStore'])->middleware('auth');

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

Route::get('/manager/request-access/excel', [CollectionController::class, 'requestAccessExcel'])
    ->middleware('auth')
    ->name('manager.request.access.excel');

Route::get('/manager/request-access/pdf', [CollectionController::class, 'requestAccessPdf'])
    ->middleware('auth')
    ->name('manager.request.access.pdf');

Route::get('/manager/collection', [CollectionController::class, 'managerCollection'])
    ->name('manager.collection');

Route::get('/manager/ar-accounts', [CollectionController::class, 'arAccounts'])
    ->middleware('auth')
    ->name('manager.ar.accounts');

Route::post('/manager/ar-payment/{id}', [CollectionController::class, 'payAr'])
    ->middleware('auth')
    ->name('manager.ar.pay');

Route::get('/manager/delivery-report', [InventoryController::class, 'deliveryReport'])
    ->name('manager.delivery.report');

Route::get('/delivery-report/excel', [InventoryController::class, 'deliveryReportExcel'])
    ->name('manager.delivery.report.excel');

Route::get('/defective-stock', [InventoryController::class, 'defectiveIndex'])
    ->name('manager.defective.index');

Route::post('/defective-stock/store', [InventoryController::class, 'defectiveStore'])
    ->name('manager.defective.store');

Route::get('/manager/delivery-fee', [CollectionController::class, 'deliveryFeeForm'])
    ->middleware('auth')
    ->name('manager.delivery.fee');

Route::post('/manager/delivery-fee/store', [CollectionController::class, 'deliveryStore'])
    ->middleware('auth')
    ->name('manager.delivery.store');

Route::get('/manager/delivery-today', [CollectionController::class, 'deliveryToday'])
    ->middleware('auth')
    ->name('manager.delivery.today');

Route::get('/manager/delivery-today/excel', [CollectionController::class, 'deliveryExcel'])
    ->middleware('auth')
    ->name('manager.delivery.excel');

Route::get('/manager/total-cash', [CashierController::class, 'totalCash'])
    ->middleware('auth')
    ->name('manager.total.cash');
Route::get('/manager/cash-flow-excel', [CashierController::class, 'cashFlowExcel'])
    ->middleware('auth')
    ->name('manager.cashflow.excel');

Route::get('/manager/cash-transfer', [CashTransferController::class, 'index'])->middleware('auth');
Route::post('/manager/cash-transfer/store', [CashTransferController::class, 'store'])->middleware('auth');
Route::post('/manager/cash-transfer/{id}/accept', [CashTransferController::class, 'accept'])->middleware('auth');

Route::get('/manager/store-expenses', [ExpenseController::class, 'index'])->middleware('auth');

Route::get('/manager/store-expenses', [ExpenseController::class, 'index'])->middleware('auth')->name('manager.expenses');
Route::post('/manager/store-expenses/store', [ExpenseController::class, 'store'])->middleware('auth')->name('manager.expenses.store');
Route::get('/manager/store-expenses/list', [ExpenseController::class, 'list'])->middleware('auth')->name('manager.expenses.list');
Route::get('/manager/store-expenses/excel', [ExpenseController::class, 'excel'])->middleware('auth')->name('manager.expenses.excel');

// =====================
// ADMIN GROUP
// =====================
Route::prefix('admin')->middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/sync-products', [InventoryController::class, 'syncProducts']);
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
    Route::get('/sales/brand/pdf', [SalesReportController::class, 'brandPdf'])->name('report.brand.pdf');
    Route::get('/sales/brand/excel', [SalesReportController::class, 'brandExcel'])->name('report.brand.excel');

    Route::get('/collection/export/pdf', [CollectionController::class, 'exportPdf'])->name('admin.collection.export.pdf');
    Route::get('/collection/export/excel', [CollectionController::class, 'exportExcel'])->name('admin.collection.export.excel');
    
    Route::get('/delivery-fee', [CollectionController::class, 'deliveryFeeForm']);
    Route::post('/delivery-fee/store', [CollectionController::class, 'deliveryStore']);

    Route::get('/delivery-today', [CollectionController::class, 'deliveryToday']);
    Route::get('/delivery-today/excel', [CollectionController::class, 'deliveryExcel']);

    Route::get('/ar-accounts', [CollectionController::class, 'arAccounts']);
    Route::post('/ar-payment/{id}', [CollectionController::class, 'payAr']);

    
    Route::get('/total-cash', [CashierController::class, 'totalCash'])
    ->name('admin.total.cash');

    Route::get('/cash-flow-excel', [CashierController::class, 'cashFlowExcel'])
    ->name('admin.cashflow.excel');

    Route::get('/cash-transfer', [CashTransferController::class, 'index']);
Route::post('/cash-transfer/store', [CashTransferController::class, 'store']);
Route::post('/cash-transfer/{id}/accept', [CashTransferController::class, 'accept']);

Route::get('/store-expenses', [ExpenseController::class, 'index']);
Route::post('/store-expenses/store', [ExpenseController::class, 'store']);
Route::get('/store-expenses/list', [ExpenseController::class, 'list']);
Route::get('/store-expenses/excel', [ExpenseController::class, 'excel']);

Route::get('/store-expenses', [ExpenseController::class, 'index'])->name('admin.expenses');
Route::post('/store-expenses/store', [ExpenseController::class, 'store'])->name('admin.expenses.store');
Route::get('/store-expenses/list', [ExpenseController::class, 'list'])->name('admin.expenses.list');
Route::get('/store-expenses/excel', [ExpenseController::class, 'excel'])->name('admin.expenses.excel');

});// end admin group

    Route::post('/stock/store', [InventoryController::class, 'store'])
    ->middleware('auth')
    ->name('stock.store');


// =====================
// 🔥 CASHIER
// =====================
Route::prefix('cashier')->middleware('auth')->group(function () {

    Route::get('/', [CashierController::class, 'index'])->name('cashier.dashboard');
    Route::post('/checkout', [CashierController::class, 'checkout']);

    Route::get('/inventory-stock', [InventoryController::class, 'overviewStock'])->name('cashier.inventory.stock');

    Route::get('/collection-receipt', [CollectionController::class, 'create'])->name('cashier.collection.create');
    Route::post('/collection-receipt', [CollectionController::class, 'store'])->name('cashier.collection.store');

    Route::get('/transfer-in', [InventoryController::class, 'transferInForm'])->name('cashier.transfer.in');
    Route::post('/transfer-in', [InventoryController::class, 'transferInStore'])->name('cashier.transfer.in.store');

    Route::get('/incoming', [InventoryController::class, 'incoming']);
    Route::post('/receive/{id}', [InventoryController::class, 'receive']);

    Route::get('/collection-today', [CollectionController::class, 'today'])->name('cashier.collection.today');

    Route::get('/collection/export/pdf', [CollectionController::class, 'exportPdf'])->name('cashier.collection.export.pdf');
    Route::get('/collection/export/excel', [CollectionController::class, 'exportExcel'])->name('cashier.collection.export.excel');

    Route::get('/collection-cancel', [CollectionController::class, 'cancelForm'])->name('cashier.collection.cancel');
    Route::post('/collection-cancel', [CollectionController::class, 'cancelStore'])->name('cashier.collection.cancel.store');

    Route::get('/return-receipt', [CollectionController::class, 'returnForm'])->name('cashier.return.create');

    Route::get('/return/load/{receipt_no}', [CollectionController::class, 'loadReceipt'])
        ->middleware('auth')
        ->name('cashier.return.load');

    Route::post('/return-receipt', [CollectionController::class, 'returnStore'])->name('cashier.return.store');

    // ✅ FIXED DELIVERY ROUTES
    Route::get('/delivery-fee', [CollectionController::class, 'deliveryFeeForm'])
        ->name('cashier.delivery.fee');

    Route::post('/delivery-fee/store', [CollectionController::class, 'deliveryStore'])
        ->name('cashier.delivery.store');

    Route::get('/delivery/load/{receipt_no}', [CollectionController::class, 'loadReceipt'])
        ->middleware('auth')
        ->name('cashier.delivery.load');

    Route::get('/delivery-today', [CollectionController::class, 'deliveryToday'])
        ->name('cashier.delivery.today');

    Route::get('/delivery-today/excel', [CollectionController::class, 'deliveryExcel'])
        ->name('cashier.delivery.excel');

  Route::get('/ar-accounts', [CollectionController::class, 'arAccounts'])
    ->name('cashier.ar.accounts');

  Route::post('/ar-accounts/payment/{id}', [CollectionController::class, 'payAr'])
    ->name('cashier.ar.payment');

    Route::get('/deposit', [CollectionController::class, 'deposit'])->name('cashier.deposit');
    Route::post('/deposit', [CollectionController::class, 'depositStore'])->name('cashier.deposit.store');

   Route::get('/cash-total', [CashierController::class, 'totalCash'])
    ->name('cashier.total.cash');

    Route::get('/cash-transfer', [CashTransferController::class, 'index'])
    ->name('cashier.transfer.cash');

    Route::post('/cash-transfer/store', [CashTransferController::class, 'store'])
    ->name('cashier.transfer.cash.store');

    Route::post('/cash-transfer/{id}/accept', [CashTransferController::class, 'accept'])
    ->name('cashier.transfer.cash.accept');

   Route::get('/expenses', [ExpenseController::class, 'index'])
    ->name('cashier.expenses');

    Route::post('/expenses/store', [ExpenseController::class, 'store'])
    ->name('cashier.expenses.store');

    Route::get('/expenses/list', [ExpenseController::class, 'list'])
    ->name('cashier.expenses.list');

    Route::get('/expenses/excel', [ExpenseController::class, 'excel'])
    ->name('cashier.expenses.excel');

    Route::get('/total-cash', [CashierController::class, 'totalCash'])
    ->name('cashier.total.cash');

    Route::get('/cash-flow-excel', [CashierController::class, 'cashFlowExcel'])
    ->name('cashier.cashflow.excel');

    Route::get('/salary', function () {
        return 'Employee Salary Page';
    })->name('cashier.salary');

    Route::get('/change-password', [AuthController::class, 'showChangePassword'])
        ->name('cashier.password');

    Route::post('/change-password', [AuthController::class, 'changePassword'])
        ->name('cashier.password.update');

    Route::get('/ar-accounts', [CollectionController::class, 'arAccounts']);
    Route::post('/ar-payment/{id}', [CollectionController::class, 'payAr']);


    
    Route::get('/add-stock', [InventoryController::class, 'create'])
        ->name('cashier.add.stock');

    Route::post('/add-stock', [InventoryController::class, 'store'])
        ->name('cashier.add.stock.store');


});


// =====================
// OTHER
// =====================
Route::get('/inventory-dashboard', function () {
    return view('inventory.dashboard');
});

Route::get('/announcements', [AnnouncementController::class, 'index'])
    ->middleware('auth')
    ->name('announcements.index');

Route::post('/announcements/store', [AnnouncementController::class, 'store'])
    ->middleware('auth')
    ->name('announcements.store');

Route::delete('/announcements/delete/{id}', [AnnouncementController::class, 'destroy'])
    ->middleware('auth')
    ->name('announcements.delete');

/* READ ANNOUNCEMENTS = PERMANENT RESET BELL COUNT */
Route::post('/announcements/read', function () {

    $userId = auth()->id();

    $ids = \App\Models\Announcement::where('is_active', 1)
        ->pluck('id');

    foreach ($ids as $id) {
        DB::table('announcement_reads')->updateOrInsert(
            [
                'user_id' => $userId,
                'announcement_id' => $id
            ],
            [
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    return response()->json([
        'success' => true
    ]);

})->middleware('auth')->name('announcements.read');


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