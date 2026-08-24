<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PublicController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\VehicleWebController;
use App\Http\Controllers\Web\BookingWebController;
use App\Http\Controllers\Web\DriverWebController;
use App\Http\Controllers\Web\InvoiceWebController;
use App\Http\Controllers\Web\InspectionWebController;
use App\Http\Controllers\Web\TripReportWebController;
use App\Http\Controllers\Web\SalaryWebController;
use App\Http\Controllers\Web\ReplacementWebController;
use App\Http\Controllers\Web\SuperadminController;
use App\Http\Controllers\Web\ElektronikController;
use App\Http\Controllers\Web\ContactWebController;
use App\Http\Controllers\Web\MailWebController;
use App\Http\Controllers\Web\ChatWebController;
use App\Http\Controllers\Web\ContactDirectoryWebController;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/tentang-kami', [PublicController::class, 'about'])->name('about');
Route::get('/produk', [PublicController::class, 'products'])->name('products');
Route::get('/kontak', [PublicController::class, 'contact'])->name('contact');
Route::post('/kontak', [ContactWebController::class, 'submit'])->name('contact.submit');
Route::get('/vehicle/{slug}', [PublicController::class, 'show'])->name('public.vehicle');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::put('/password', [DashboardController::class, 'updatePassword'])->name('dashboard.password.update');
});

// Communication Routes (Mail, Chat, Contacts)
Route::middleware('auth')->group(function () {
    // Mail Inbox
    Route::prefix('mail')->group(function () {
        Route::get('/', [MailWebController::class, 'index'])->name('mail.index');
        Route::get('/compose', [MailWebController::class, 'compose'])->name('mail.compose');
        Route::post('/send', [MailWebController::class, 'store'])->name('mail.store');
        Route::get('/contacts-inbox', [ContactWebController::class, 'index'])->name('mail.contacts');
        Route::get('/contacts-inbox/{contactMessage}', [ContactWebController::class, 'show'])->name('mail.contacts.show');
        Route::post('/contacts-inbox/{contactMessage}/reply', [ContactWebController::class, 'reply'])->name('mail.contacts.reply');
        Route::delete('/contacts-inbox/{contactMessage}', [ContactWebController::class, 'destroy'])->name('mail.contacts.destroy');
        Route::get('/{message}', [MailWebController::class, 'show'])->name('mail.show');
        Route::post('/{message}/star', [MailWebController::class, 'toggleStar'])->name('mail.star');
        Route::post('/{message}/trash', [MailWebController::class, 'toggleTrash'])->name('mail.trash');
        Route::delete('/{message}', [MailWebController::class, 'destroy'])->name('mail.destroy');
    });

    // Live Chat Messenger
    Route::prefix('chat')->group(function () {
        Route::get('/', [ChatWebController::class, 'index'])->name('chat.index');
        Route::post('/{conversation}/send', [ChatWebController::class, 'send'])->name('chat.send');
        Route::get('/{conversation}/messages', [ChatWebController::class, 'fetchMessages'])->name('chat.messages');
    });

    // Contacts Directory
    Route::get('/contacts', [ContactDirectoryWebController::class, 'index'])->name('contacts.index');
});

// Superadmin Routes
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->group(function () {
    Route::get('/monitoring', [SuperadminController::class, 'monitoring'])->name('superadmin.monitoring');
    Route::get('/finance', [SuperadminController::class, 'finance'])->name('superadmin.finance');
    Route::get('/absen', [SuperadminController::class, 'absen'])->name('superadmin.absen');
    Route::get('/monitoring-vehicle', [SuperadminController::class, 'monitoringVehicle'])->name('superadmin.monitoring-vehicle');
    Route::get('/motor', [SuperadminController::class, 'motor'])->name('superadmin.motor');
    Route::get('/elektronik', [SuperadminController::class, 'elektronik'])->name('superadmin.elektronik');

    Route::get('/elektronik/{type}', [ElektronikController::class, 'index'])->name('superadmin.elektronik.type');
    Route::get('/elektronik/{type}/create', [ElektronikController::class, 'create'])->name('superadmin.elektronik.create');
    Route::post('/elektronik/{type}', [ElektronikController::class, 'store'])->name('superadmin.elektronik.store');
    Route::get('/elektronik/{type}/{id}/edit', [ElektronikController::class, 'edit'])->name('superadmin.elektronik.edit');
    Route::put('/elektronik/{type}/{id}', [ElektronikController::class, 'update'])->name('superadmin.elektronik.update');
    Route::delete('/elektronik/{type}/{id}', [ElektronikController::class, 'destroy'])->name('superadmin.elektronik.destroy');
});

// Vehicles (Mobil) Routes
Route::middleware(['auth', 'role:superadmin,owner'])->prefix('vehicles')->group(function () {
    Route::get('/', [VehicleWebController::class, 'index'])->name('vehicles.index');
    Route::get('/create', [VehicleWebController::class, 'create'])->name('vehicles.create');
    Route::post('/', [VehicleWebController::class, 'store'])->name('vehicles.store');
    Route::get('/{vehicle}/edit', [VehicleWebController::class, 'edit'])->name('vehicles.edit');
    Route::put('/{vehicle}', [VehicleWebController::class, 'update'])->name('vehicles.update');
    Route::delete('/{vehicle}', [VehicleWebController::class, 'destroy'])->name('vehicles.destroy');
});

// Motors Routes
Route::middleware(['auth', 'role:superadmin,owner'])->prefix('motors')->group(function () {
    Route::get('/', [VehicleWebController::class, 'motor'])->name('motors.index');
});

Route::middleware('auth')->prefix('bookings')->group(function () {
    Route::get('/', [BookingWebController::class, 'index'])->name('bookings.index');
    Route::get('/create', [BookingWebController::class, 'create'])->name('bookings.create');
    Route::post('/', [BookingWebController::class, 'store'])->name('bookings.store');
    Route::get('/{booking}', [BookingWebController::class, 'show'])->name('bookings.show');
    Route::post('/{booking}/ktp', [BookingWebController::class, 'uploadKtp'])->name('bookings.upload-ktp');
    Route::post('/{booking}/confirm', [BookingWebController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/{booking}/cancel', [BookingWebController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/{booking}/start-trip', [BookingWebController::class, 'startTrip'])->name('bookings.start');
    Route::post('/{booking}/complete', [BookingWebController::class, 'complete'])->name('bookings.complete');
});

Route::middleware(['auth', 'role:superadmin,owner'])->prefix('drivers')->group(function () {
    Route::get('/', [DriverWebController::class, 'index'])->name('drivers.index');
    Route::get('/create', [DriverWebController::class, 'create'])->name('drivers.create');
    Route::post('/', [DriverWebController::class, 'store'])->name('drivers.store');
    Route::get('/{driver}/edit', [DriverWebController::class, 'edit'])->name('drivers.edit');
    Route::put('/{driver}', [DriverWebController::class, 'update'])->name('drivers.update');
    Route::delete('/{driver}', [DriverWebController::class, 'destroy'])->name('drivers.destroy');
});

Route::middleware('auth')->prefix('invoices')->group(function () {
    Route::get('/', [InvoiceWebController::class, 'index'])->name('invoices.index');
    Route::get('/{invoice}', [InvoiceWebController::class, 'show'])->name('invoices.show');
    Route::post('/{invoice}/pay', [InvoiceWebController::class, 'pay'])->name('invoices.pay');
});

Route::middleware('auth')->prefix('inspections')->group(function () {
    Route::get('/', [InspectionWebController::class, 'index'])->name('inspections.index');
    Route::get('/create', [InspectionWebController::class, 'create'])->name('inspections.create');
    Route::post('/', [InspectionWebController::class, 'store'])->name('inspections.store');
    Route::get('/{inspection}', [InspectionWebController::class, 'show'])->name('inspections.show');
});

Route::middleware('auth')->prefix('trip-reports')->group(function () {
    Route::get('/', [TripReportWebController::class, 'index'])->name('reports.index');
    Route::get('/create', [TripReportWebController::class, 'create'])->name('reports.create');
    Route::post('/', [TripReportWebController::class, 'store'])->name('reports.store');
    Route::get('/{tripReport}', [TripReportWebController::class, 'show'])->name('reports.show');
});

Route::middleware(['auth', 'role:superadmin,owner'])->prefix('salaries')->group(function () {
    Route::get('/', [SalaryWebController::class, 'index'])->name('salaries.index');
    Route::get('/create', [SalaryWebController::class, 'create'])->name('salaries.create');
    Route::post('/', [SalaryWebController::class, 'store'])->name('salaries.store');
    Route::get('/{salary}', [SalaryWebController::class, 'show'])->name('salaries.show');
    Route::post('/{salary}/approve', [SalaryWebController::class, 'approve'])->name('salaries.approve');
    Route::post('/{salary}/pay', [SalaryWebController::class, 'pay'])->name('salaries.pay');
});

Route::middleware('auth')->prefix('replacements')->group(function () {
    Route::get('/', [ReplacementWebController::class, 'index'])->name('replacements.index');
    Route::get('/create', [ReplacementWebController::class, 'create'])->name('replacements.create');
    Route::post('/', [ReplacementWebController::class, 'store'])->name('replacements.store');
    Route::post('/{replacement}/approve', [ReplacementWebController::class, 'approve'])->name('replacements.approve');
    Route::post('/{replacement}/reject', [ReplacementWebController::class, 'reject'])->name('replacements.reject');
});
