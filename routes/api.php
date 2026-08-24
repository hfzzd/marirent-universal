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

    Route::apiResource('bookings', BookingController::class)->only(['index', 'store']);
    Route::get('/bookings/{bookingCode}', [BookingController::class, 'show']);
    Route::post('/bookings/{bookingCode}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{bookingCode}/start', [BookingController::class, 'startTrip']);
    Route::post('/bookings/{bookingCode}/complete', [BookingController::class, 'complete']);
    Route::post('/bookings/{bookingCode}/cancel', [BookingController::class, 'cancel']);

    Route::apiResource('drivers', DriverController::class);

    Route::apiResource('inspections', InspectionController::class)->only(['index', 'store', 'show']);
    Route::get('/inspections/booking/{bookingCode}', [InspectionController::class, 'byBooking']);

    Route::apiResource('trip-reports', TripReportController::class)->only(['index', 'store', 'show']);
    Route::put('/trip-reports/{tripReport}', [TripReportController::class, 'update']);

    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'store', 'show']);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
    Route::get('/invoices/booking/{bookingCode}', [InvoiceController::class, 'byBooking']);

    Route::apiResource('rentals', RentalController::class)->only(['index', 'store', 'show']);
    Route::post('/rentals/{rental}/cancel', [RentalController::class, 'cancel']);
    Route::post('/rentals/{rental}/confirm', [RentalController::class, 'confirm']);
    Route::post('/rentals/{rental}/complete', [RentalController::class, 'complete']);
    Route::post('/rentals/{rental}/replace-vehicle', [RentalController::class, 'replaceVehicle']);

    Route::apiResource('salaries', SalaryController::class)->only(['index', 'store', 'show']);
    Route::post('/salaries/{salary}/approve', [SalaryController::class, 'approve']);
    Route::post('/salaries/{salary}/pay', [SalaryController::class, 'pay']);

    Route::apiResource('payments', PaymentController::class)->only(['index', 'store']);
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify']);
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject']);
});
