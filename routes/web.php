<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\CarpetController;
use App\Http\Controllers\Backend\LaundryController;
use App\Http\Controllers\Backend\MpesaController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\CustomerRetentionController;
use App\Http\Controllers\Home\ContactController;
use App\Http\Controllers\Home\AboutController;
use App\Http\Controllers\Home\ServiceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Route::get('/dashboard', function () {
//     return view('admin.index');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [CarpetController::class, 'CarpetDashboard'])
    ->middleware(['auth']) // removed 'verified'
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::controller(AdminController::class)->middleware(['auth'])->group(function () {
    Route::get('/admin/logout', 'destroy')->name('admin.logout');
    Route::get('/admin/profile', 'Profile')->name('admin.profile');
    Route::get('/edit/profile', 'EditProfile')->name('edit.profile');
    Route::post('/store/profile', 'StoreProfile')->name('store.profile');
    Route::get('/change/password', 'ChangePassword')->name('change.password');
    Route::post('/update/password', 'UpdatePassword')->name('update.password');
});


/// Carpet All Route
Route::controller(CarpetController::class)->middleware(['auth'])->group(function(){

    Route::get('/all/carpet','AllCarpet')->name('all.carpet')->middleware('permission:carpet.all');
    Route::get('/carpets/data','getCarpetsData')->name('carpets.data')->middleware('permission:carpet.all');
    Route::get('/add/carpet','AddCarpet')->name('add.carpet')->middleware('permission:carpet.add');
    Route::post('/store/carpet','StoreCarpet')->name('carpet.store');
    Route::get('/history/carpet/{phone}','HistoryCarpet')->name('history.client');
    Route::get('/edit/carpet/{id}','EditCarpet')->name('edit.carpet');
    Route::post('/update/carpet','UpdateCarpet')->name('carpet.update');
    Route::delete('/delete/carpet/{id}','DeleteCarpet')->name('delete.carpet');
    Route::get('/details/carpet/{id}','DetailsCarpet')->name('details.carpet');
    Route::get('/trashed','TrashedItems')->name('trashed.index')->middleware('can:admin.all');
    Route::post('/restore/carpet/{id}','RestoreCarpet')->name('restore.carpet')->middleware('can:admin.all');
    Route::delete('/force-delete/carpet/{id}','ForceDeleteCarpet')->name('force.delete.carpet')->middleware('can:admin.all');
    Route::get('/customer/by-phone','getCustomerByPhone')->name('customer.byPhone');
    Route::get('/customer/by-uniqueid','getCustomerByUniqueId')->name('customer.byUniqueId');
    Route::get('reports/carpets/download-all','downloadAllCarpets')->name('reports.carpets.downloadAll');
    Route::get('reports/carpets/month','viewCarpetsByMonth')->name('reports.carpets.viewMonth');
    Route::get('reports/carpets/month/download','downloadCarpetsByMonth')->name('reports.carpets.downloadMonth');
    Route::get('reports/carpets/month/new/download','downloadNewCarpetsByMonth')->name('reports.carpets.downloadNewMonth');


    });

    /// Laundry All Route
Route::controller(LaundryController::class)->middleware(['auth'])->group(function(){

    Route::get('/all/laundry','AllLaundry')->name('all.laundry')->middleware('permission:laundry.all');
    Route::get('/laundries/data','getLaundriesData')->name('laundries.data')->middleware('permission:laundry.all');
    Route::get('/add/laundry','AddLaundry')->name('add.laundry')->middleware('permission:laundry.add');
    Route::post('/store/laundry','StoreLaundry')->name('laundry.store');
    Route::get('/edit/laundry/{id}','EditLaundry')->name('edit.laundry');
    Route::post('/update/laundry','UpdateLaundry')->name('laundry.update');
    Route::delete('/delete/laundry/{id}','DeleteLaundry')->name('delete.laundry');
    Route::get('/details/laundry/{id}','DetailsLaundry')->name('details.laundry');
    Route::post('/restore/laundry/{id}','RestoreLaundry')->name('restore.laundry')->middleware('can:admin.all');
    Route::delete('/force-delete/laundry/{id}','ForceDeleteLaundry')->name('force.delete.laundry')->middleware('can:admin.all');
    Route::get('reports/laundry/download-all','downloadAllLaundry')->name('reports.laundry.downloadAll');
    Route::get('reports/laundry/month','viewLaundryByMonth')->name('reports.laundry.viewMonth');
    Route::get('reports/laundry/month/download','downloadLaundryByMonth')->name('reports.laundry.downloadMonth');
    Route::get('reports/laundry/month/new/download','downloadNewLaundryByMonth')->name('reports.laundry.downloadNewMonth');

    });

     /// Mpesa All Route
Route::controller(MpesaController::class)->middleware(['auth'])->group(function(){

    Route::get('/all/mpesa','AllMpesa')->name('all.mpesa')->middleware('permission:mpesa.all');;
    Route::get('/add/mpesa','AddMpesa')->name('add.mpesa')->middleware('permission:mpesa.add');;
    Route::post('/store/mpesa','StoreMpesa')->name('mpesa.store');
    Route::get('/edit/mpesa/{id}','EditMpesa')->name('edit.mpesa');
    Route::post('/update/mpesa','UpdateMpesa')->name('mpesa.update');
    Route::get('/delete/mpesa/{id}','DeleteMpesa')->name('delete.mpesa');
    Route::get('reports/mpesa/download-all','downloadAllMpesa')->name('reports.mpesa.downloadAll');

    });

    /// Report All Route
Route::controller(ReportController::class)->middleware(['auth'])->group(function(){

    Route::get('reports/carpets/today','CarpetsToday')->name('reports.carpets.today');
    Route::get('reports/laundry/today','LaundryToday')->name('reports.laundry.today');
    Route::get('reports/mpesa/today','MpesaToday')->name('reports.mpesa.today');
    Route::get('reports/landing','index')->name('reports.specific_report');
    Route::post('reports/landing/handle','handle')->name('reports.specific_report.handle');
    Route::get('reports/performance','performance')->name('reports.performance');
    Route::post('reports/performance-data','performanceData')->name('reports.performance.data');
    Route::get('reports/carpets/pending','pendingCarpets')->name('reports.carpets.pending');

    });

// Customer Retention Routes
Route::controller(CustomerRetentionController::class)->middleware(['auth'])->group(function(){
    Route::get('/customer-retention', 'index')->name('customer.retention.index')->middleware('permission:mpesa.compare');
    Route::get('/customer-retention/export', 'export')->name('customer.retention.export')->middleware('permission:mpesa.compare');
    Route::post('/customer-retention/follow-up', 'generateFollowUpList')->name('customer.retention.followup')->middleware('permission:mpesa.compare');
});

    // Contact All Route
Route::controller(ContactController::class)->group(function () {
    Route::get('/contact', 'Contact')->name('contact.me');
    Route::post('/store/message', 'StoreMessage')->name('store.message');

});

Route::controller(AboutController::class)->group(function () {
    Route::get('/about/page', 'AboutPage')->name('about.page')->middleware('auth');
    Route::post('/update/about', 'UpdateAbout')->name('update.about')->middleware(['auth', 'can:admin.all']);
    Route::get('/about', 'HomeAbout')->name('home.about');

//     Route::get('/about/multi/image', 'AboutMultiImage')->name('about.multi.image');
//     Route::post('/store/multi/image', 'StoreMultiImage')->name('store.multi.image');

//     Route::get('/all/multi/image', 'AllMultiImage')->name('all.multi.image');
//     Route::get('/edit/multi/image/{id}', 'EditMultiImage')->name('edit.multi.image');

//     Route::post('/update/multi/image', 'UpdateMultiImage')->name('update.multi.image');
//    Route::get('/delete/multi/image/{id}', 'DeleteMultiImage')->name('delete.multi.image');

});

 // Service Page All Route
 Route::controller(ServiceController::class)->group(function () {
    Route::get('/service-details', 'ServicePage')->name('service.page');
    Route::post('/store/message', 'StoreMessage')->name('store.message');

});

///Permission All Route
Route::controller(RoleController::class)->middleware(['auth', 'can:admin.all'])->group(function(){

    Route::get('/all/permission','AllPermission')->name('all.permission');
    Route::get('/add/permission','AddPermission')->name('add.permission');
    Route::post('/store/permission','StorePermission')->name('permission.store');
    Route::get('/edit/permission/{id}','EditPermission')->name('edit.permission');
    Route::post('/update/permission','UpdatePermission')->name('permission.update');
    Route::get('/delete/permission/{id}','DeletePermission')->name('delete.permission');
});

///Roles All Route
Route::controller(RoleController::class)->middleware(['auth', 'can:admin.all'])->group(function(){

    Route::get('/all/roles','AllRoles')->name('all.roles');
    Route::get('/add/roles','AddRoles')->name('add.roles');
    Route::post('/store/roles','StoreRoles')->name('roles.store');
    Route::get('/edit/roles/{id}','EditRoles')->name('edit.roles');
    Route::post('/update/roles','UpdateRoles')->name('roles.update');
    Route::get('/delete/roles/{id}','DeleteRoles')->name('delete.roles');
});

///Add Roles in Permission All Route
Route::controller(RoleController::class)->middleware(['auth', 'can:admin.all'])->group(function(){

    Route::get('/add/roles/permission','AddRolesPermission')->name('add.roles.permission');
    Route::post('/role/permission/store','StoreRolesPermission')->name('role.permission.store');
    Route::get('/all/roles/permission','AllRolesPermission')->name('all.roles.permission');
    Route::get('/admin/edit/roles/{id}','AdminEditRoles')->name('admin.edit.roles');
    Route::post('/role/permission/update/{id}','RolePermissionUpdate')->name('role.permission.update');
    Route::get('/admin/delete/roles/{id}','AdminDeleteRoles')->name('admin.delete.roles');

});

///Admin User All Route
Route::controller(AdminController::class)->middleware(['auth', 'can:admin.all'])->group(function(){

    Route::get('/all/admin','AllAdmin')->name('all.admin');
    Route::get('/add/admin','AddAdmin')->name('add.admin');
    Route::post('/store/admin','StoreAdmin')->name('admin.store');
    Route::get('/edit/admin/{id}','EditAdmin')->name('edit.admin');
    Route::post('/update/admin','UpdateAdmin')->name('admin.update');
    Route::get('/delete/admin/{id}','DeleteAdmin')->name('delete.admin');

    // Suspend/Activate Admin
    Route::get('/suspend/admin/{id}','SuspendAdmin')->name('suspend.admin');
    Route::get('/activate/admin/{id}','ActivateAdmin')->name('activate.admin');

    // Change Admin Password (Superadmin only)
    Route::get('/change/admin/password/{id}','ChangeAdminPassword')->name('change.admin.password');
    Route::post('/update/admin/password','UpdateAdminPassword')->name('update.admin.password');

    // Database Backup
    Route::get('/database/backup','DatabaseBackup')->name('database.backup');
    Route::get('/backup/now','BackupNow')->name('backup.now');
    Route::get('download/{getFilename}','DownloadDatabase')->name('download.database');
    Route::get('/delete/database/{getFilename}','DeleteDatabase')->name('delete.database');

   });

// Audit Trail Routes
Route::controller(App\Http\Controllers\Backend\AuditController::class)->middleware(['auth'])->group(function(){
    Route::get('/audit', 'index')->name('audit.index')->middleware('permission:mpesa.compare');
    Route::get('/audit/{audit}', 'show')->name('audit.show')->middleware('permission:mpesa.compare');
    Route::get('/audit-stats', 'stats')->name('audit.stats')->middleware('permission:mpesa.compare');
    Route::get('/audit/export', 'export')->name('audit.export')->middleware('permission:mpesa.compare');
});

// Notification Routes
Route::controller(App\Http\Controllers\Backend\NotificationController::class)->middleware(['auth'])->group(function(){
    Route::get('/notifications', 'index')->name('notifications.index');
    Route::get('/notifications/unread', 'unread')->name('notifications.unread');
    Route::post('/notifications/{id}/read', 'markAsRead')->name('notifications.read');
    Route::post('/notifications/mark-all-read', 'markAllAsRead')->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', 'destroy')->name('notifications.delete');
    Route::get('/notifications/overdue', 'overdueDeliveries')->name('notifications.overdue');
    Route::post('/notifications/{id}/quick-update', 'quickUpdate')->name('notifications.quick-update');
});

// Expense Routes
Route::controller(App\Http\Controllers\Backend\ExpenseController::class)->middleware(['auth'])->group(function(){
    Route::get('/expenses', 'index')->name('expenses.index')->middleware('permission:mpesa.all');
    Route::get('/expenses/create', 'create')->name('expenses.create')->middleware('permission:mpesa.add');
    Route::get('/expenses/create-new', 'createNew')->name('expenses.create.new');
    Route::post('/expenses/store-new', 'storeNew')->name('expenses.store.new');
    Route::post('/expenses', 'store')->name('expenses.store');
    Route::get('/expenses/{expense}', 'show')->name('expenses.show');
    Route::get('/expenses/{expense}/edit', 'edit')->name('expenses.edit');
    Route::put('/expenses/{expense}', 'update')->name('expenses.update');
    Route::delete('/expenses/{expense}', 'destroy')->name('expenses.destroy');
    Route::get('/expenses/{expense}/receipt', 'serveReceipt')->name('expense.receipt');
    Route::post('/expenses/quick-add', 'quickAdd')->name('expenses.quickAdd');
    Route::post('/expenses/{expense}/approve', 'approve')->name('expenses.approve');
    Route::post('/expenses/{expense}/reject', 'reject')->name('expenses.reject');
    Route::get('/expenses-dashboard', 'dashboard')->name('expenses.dashboard');
    Route::get('reports/expenses/month', 'viewExpensesByMonth')->name('reports.expenses.viewMonth');
    Route::get('reports/expenses/month/download', 'downloadExpensesByMonth')->name('reports.expenses.downloadMonth');
});

// Reports API Routes (for performance dashboard)
Route::post('/api/performance/data', [ReportController::class, 'performanceData'])
    ->name('api.performance.data')
    ->middleware(['auth']);

// SMS Routes
Route::controller(App\Http\Controllers\Backend\SmsController::class)->middleware('auth')->group(function () {
    Route::get('/sms/dashboard', 'dashboard')->name('sms.dashboard');
    Route::get('/sms/send', 'sendForm')->name('sms.send');
    Route::post('/sms/send-single', 'sendSingle')->name('sms.sendSingle')->middleware('throttle:10,1');
    Route::get('/sms/bulk', 'bulkForm')->name('sms.bulk');
    Route::post('/sms/preview-recipients', 'previewRecipients')->name('sms.previewRecipients');
    Route::post('/sms/send-bulk', 'sendBulk')->name('sms.sendBulk')->middleware('throttle:3,1');
    Route::post('/sms/send-to-carpet/{id}', 'sendToCarpet')->name('sms.sendToCarpet')->middleware('throttle:10,1');
    Route::get('/sms/balance', 'getBalance')->name('sms.balance');
    Route::get('/sms/history', 'history')->name('sms.history');
    Route::get('/sms/statistics', 'statistics')->name('sms.statistics');
});

// SMS Webhook Routes (No authentication required - called by Roberms)
Route::controller(App\Http\Controllers\Backend\SmsWebhookController::class)->group(function () {
    Route::post('/sms/webhook/delivery', 'handleDeliveryReport')->name('sms.webhook.delivery');
    Route::get('/sms/delivery-status/{uniqueIdentifier}', 'checkDeliveryStatus')->name('sms.deliveryStatus')->middleware('auth');
});

require __DIR__.'/auth.php';
