<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\InspectionController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\TripReportController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/public', [VehicleController::class, 'publicList']);
Route::get('/vehicles/{slug}', [VehicleController::class, 'show']);
Route::get('/categories', [VehicleController::class, 'categories']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read']);

    Route::apiResource('bookings', BookingController::class)->only(['index', 'store'])->names([
        'index' => 'api.bookings.index',
        'store' => 'api.bookings.store',
    ]);
    Route::get('/bookings/{bookingCode}', [BookingController::class, 'show']);
    Route::post('/bookings/{bookingCode}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{bookingCode}/start', [BookingController::class, 'startTrip']);
    Route::post('/bookings/{bookingCode}/complete', [BookingController::class, 'complete']);
    Route::post('/bookings/{bookingCode}/cancel', [BookingController::class, 'cancel']);

    Route::apiResource('drivers', DriverController::class)->names([
        'index' => 'api.drivers.index',
        'store' => 'api.drivers.store',
        'show' => 'api.drivers.show',
        'update' => 'api.drivers.update',
        'destroy' => 'api.drivers.destroy',
    ]);

    Route::apiResource('inspections', InspectionController::class)->only(['index', 'store', 'show'])->names([
        'index' => 'api.inspections.index',
        'store' => 'api.inspections.store',
        'show' => 'api.inspections.show',
    ]);

    Route::apiResource('trip-reports', TripReportController::class)->only(['index', 'store', 'show'])->names([
        'index' => 'api.trip-reports.index',
        'store' => 'api.trip-reports.store',
        'show' => 'api.trip-reports.show',
    ]);

    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'store', 'show'])->names([
        'index' => 'api.invoices.index',
        'store' => 'api.invoices.store',
        'show' => 'api.invoices.show',
    ]);

    Route::apiResource('rentals', RentalController::class)->only(['index', 'store', 'show'])->names([
        'index' => 'api.rentals.index',
        'store' => 'api.rentals.store',
        'show' => 'api.rentals.show',
    ]);

    Route::apiResource('salaries', SalaryController::class)->only(['index', 'store', 'show'])->names([
        'index' => 'api.salaries.index',
        'store' => 'api.salaries.store',
        'show' => 'api.salaries.show',
    ]);

    Route::apiResource('payments', PaymentController::class)->only(['index', 'store'])->names([
        'index' => 'api.payments.index',
        'store' => 'api.payments.store',
    ]);
});
