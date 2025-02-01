<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\CarpetController;
use App\Http\Controllers\Backend\LaundryController;
use App\Http\Controllers\Backend\MpesaController;
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
    return view('frontend.index');
});

Route::get('/dashboard', function () {
    return view('admin.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::controller(AdminController::class)->group(function () {
    route::get('/admin/logout', 'destroy')->name('admin.logout');
    route::get('/admin/profile', 'Profile')->name('admin.profile');
    route::get('/edit/profile', 'EditProfile')->name('edit.profile');
    route::post('/store/profile', 'StoreProfile')->name('store.profile');
    route::get('/change/password', 'ChangePassword')->name('change.password');
    route::post('/update/password', 'UpdatePassword')->name('update.password');
});


/// Carpet All Route
Route::controller(CarpetController::class)->group(function(){

    Route::get('/all/carpet','AllCarpet')->name('all.carpet');
    Route::get('/add/carpet','AddCarpet')->name('add.carpet');
    Route::post('/store/carpet','StoreCarpet')->name('carpet.store');
    Route::get('/history/carpet/{phone}','HistoryCarpet')->name('history.client');
    Route::get('/edit/carpet/{id}','EditCarpet')->name('edit.carpet');
    Route::post('/update/carpet','UpdateCarpet')->name('carpet.update');
    Route::post('/delete/carpet','DeleteCarpet')->name('delete.carpet');

    });

    /// Laundry All Route
Route::controller(LaundryController::class)->group(function(){

    Route::get('/all/laundry','AllLaundry')->name('all.laundry');
    Route::get('/add/laundry','AddLaundry')->name('add.laundry');
    Route::post('/store/laundry','StoreLaundry')->name('laundry.store');
    Route::get('/edit/laundry/{id}','EditLaundry')->name('edit.laundry');
    Route::post('/update/laundry','UpdateLaundry')->name('laundry.update');
    Route::post('/delete/laundry','DeleteLaundry')->name('delete.laundry');
    Route::get('/details/laundry/{id}','DetailsLaundry')->name('details.laundry');

    });

     /// Mpesa All Route
Route::controller(MpesaController::class)->group(function(){

    Route::get('/all/mpesa','AllMpesa')->name('all.mpesa');
    Route::get('/add/mpesa','AddMpesa')->name('add.mpesa');
    Route::post('/store/mpesa','StoreMpesa')->name('mpesa.store');
    Route::get('/edit/mpesa/{id}','EditMpesa')->name('edit.mpesa');
    Route::post('/update/mpesa','UpdateMpesa')->name('mpesa.update');
    Route::post('/delete/mpesa','DeleteMpesa')->name('delete.mpesa');

    });

    // Contact All Route
Route::controller(ContactController::class)->group(function () {
    Route::get('/contact', 'Contact')->name('contact.me');
    Route::post('/store/message', 'StoreMessage')->name('store.message');

});

Route::controller(AboutController::class)->group(function () {
    Route::get('/about/page', 'AboutPage')->name('about.page');
    Route::post('/update/about', 'UpdateAbout')->name('update.about');
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




require __DIR__.'/auth.php';
