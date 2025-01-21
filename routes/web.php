<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\CarpetController;
use App\Http\Controllers\Backend\LaundryController;
use App\Http\Controllers\Backend\MpesaController;

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
    return view('welcome');
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



require __DIR__.'/auth.php';
