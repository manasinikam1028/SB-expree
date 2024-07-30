<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\mailnotificationcontroller;
//Admin
use App\Http\Controllers\Admin\indexcontroller as AdminIndex;
use App\Http\Controllers\Admin\couriercontroller;
use App\Http\Controllers\Admin\pincodecontroller;
use App\Http\Controllers\Admin\usercontroller;
use App\Http\Controllers\Admin\bulkreportcontroller; 
use App\Http\Controllers\BarcodeController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/Home', [App\Http\Controllers\HomeController::class, 'index'])->name('Home');
    Route::middleware(['Admin'])->prefix('Admin')->group(function () {
        Route::get('/dashboard',[AdminIndex::class, 'dashboard'])->name('dashboard');
        Route::get('booking', function () {
        return view('admin.booking.bookings');
        })->name('booking');
        Route::resource('/Booking', couriercontroller::class);
        Route::get('/show/{id}', [couriercontroller::class, 'show'])->name('Booking-showdetails');
        Route::get('/invoice/{id}', [couriercontroller::class, 'invoice'])->name('Booking-invoice');
        Route::post('/BulkPincode', [couriercontroller::class, 'BulkPincode'])->name('BulkPincode');
        Route::post('/BulkBooking', [couriercontroller::class, 'BulkBooking'])->name('BulkBooking');
        Route::post('/Bulkupdate', [couriercontroller::class, 'Bulkupdate'])->name('BulkUpdate');
        Route::patch('/bookings/{id}', [couriercontroller::class,'updateall'])->name('booking.updateall');
        Route::get('Pincode', function () {
        return view('admin.pincode.index');
        })->name('Pincode');
        Route::resource('/pincode', pincodecontroller::class);
        Route::get('User', function () {
        return view('admin.user.index');
        })->name('User'); 
        Route::resource('/user', usercontroller::class)->middleware('can:users');
        Route::get('/statusupdate/{id}', [usercontroller::class, 'statusupdate'])->name('user-statusupdate');
        Route::get('bulkreport', function () {
        return view('admin.bulkreport.index');
        })->name('bulkreport');
        Route::post('/generatebulkreport', [couriercontroller::class, 'bulkreport'])->name('BulkReportGen');
        Route::get('/pancard', [pincodecontroller::class, 'pancard'])->name('user-pancard')->middleware('can:pancard');;
        Route::post('/pancard', [pincodecontroller::class, 'pancardcheck'])->name('user-pancardcheck');
    });
    Route::get('/generate-qr-code/{id}', [BarcodeController::class, 'generateQRCode'])->name('BarcodeIMG');
});
// Route::get('send-testnotification',[mailnotificationcontroller::class, 'sendnotification']);
