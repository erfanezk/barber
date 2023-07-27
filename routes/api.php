<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarberShopController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::post('register', [AuthController::class, 'register'])->name('register');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
Route::post('user-info', [AuthController::class, 'userInfo'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('country')->controller(CountryController::class)->group(function () {
        Route::get('/provinces', 'provinces');
        Route::get('/provinces/{province}/cities', 'provinceCities');
    });
    Route::prefix('barber-shop')->controller(BarberShopController::class)->group(function () {
        Route::post('/index', 'index');
        Route::get('/{barberShop}/barbers', 'barbers');
        Route::post('/', 'store');
        Route::get('/{barberShop}', 'show');
        Route::post('/join', 'join');
        Route::post('/left', 'left'); //TODO
    });
    Route::prefix('comment')->controller(CommentController::class)->group(function () {
        Route::get('/{barberShop}', 'index');
        Route::post('/', 'store');
    });
    Route::prefix('service')->controller(ServiceController::class)->group(function () {
        Route::get('/{gender}', 'index');
        Route::get('/barber/{barber}', 'barberServices');
        Route::post('add-to-barber', 'addToBarber');
        Route::post('remove-From-barber', 'removeFormBarber');
    });
    Route::prefix('appointment')->controller(AppointmentController::class)->group(function () {
        Route::get('/client/{user}', 'clientAppointment');
        Route::get('/barbershop/{barberShop}', 'barbershopAppointment');
        Route::post('list', 'appointmentList');//give me free barbershop times
        Route::post('reserve', 'reserveAppointment');
    });
});
