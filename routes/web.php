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
use App\Http\Controllers\Web\ItemReplacementWebController;
use App\Http\Controllers\Web\SuperadminController;
use App\Http\Controllers\Web\ElektronikController;
use App\Http\Controllers\Web\ContactWebController;
use App\Http\Controllers\Web\MailWebController;
use App\Http\Controllers\Web\ChatWebController;
use App\Http\Controllers\Web\ContactDirectoryWebController;
use App\Http\Controllers\Web\MaintenanceController;
use App\Http\Controllers\Web\OwnerRevenueController;
use App\Http\Controllers\Web\MerchantProfileController;

use App\Http\Controllers\Web\AttendanceWebController;
use App\Http\Controllers\Web\NotificationWebController;
use App\Http\Controllers\Web\BrandCatalogPhotoController;
use App\Http\Controllers\Web\RentalController;
use App\Http\Controllers\Web\InspectorWebController;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/tentang-kami', [PublicController::class, 'about'])->name('about');
Route::get('/produk', [PublicController::class, 'products'])->name('products');
Route::get('/kontak', [PublicController::class, 'contact'])->name('contact');
Route::post('/kontak', [ContactWebController::class, 'submit'])->name('contact.submit');
Route::get('/vehicle/{slug}', [PublicController::class, 'show'])->name('public.vehicle');
Route::get('/item/{type}/{slug}', [PublicController::class, 'showItem'])->name('public.item');
Route::get('/brands', [PublicController::class, 'brands'])->name('public.brands');
Route::get('/brand/{type}/{brand}', [PublicController::class, 'brand'])->name('public.brand');
Route::get('/store/{slug}', [PublicController::class, 'store'])->name('public.store');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/register-merchant', [AuthController::class, 'showRegisterMerchant'])->name('register.merchant');
Route::post('/register-merchant', [AuthController::class, 'registerMerchant'])->name('register.merchant.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::put('/password', [DashboardController::class, 'updatePassword'])->name('dashboard.password.update');
});

// Communication Routes (Mail, Chat, Contacts)
Route::middleware('auth')->group(function () {
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationWebController::class, 'index'])->name('notifications.index');
        Route::post('/{id}/read', [NotificationWebController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/read-all', [NotificationWebController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/unread-count', [NotificationWebController::class, 'unreadCount'])->name('notifications.unread-count');
    });

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
    Route::get('/scheduler', [SuperadminController::class, 'scheduler'])->name('superadmin.scheduler');
    Route::get('/scheduler/events', [SuperadminController::class, 'schedulerEvents'])->name('superadmin.scheduler.events');
    Route::get('/finance', [SuperadminController::class, 'finance'])->name('superadmin.finance');
    Route::get('/merchants', [SuperadminController::class, 'merchants'])->name('superadmin.merchants');
    Route::get('/merchants/create', [SuperadminController::class, 'merchantCreate'])->name('superadmin.merchants.create');
    Route::post('/merchants', [SuperadminController::class, 'merchantStore'])->name('superadmin.merchants.store');
    Route::post('/merchants/{id}/verify', [SuperadminController::class, 'merchantVerify'])->name('superadmin.merchants.verify');
    Route::post('/merchants/{id}/suspend', [SuperadminController::class, 'merchantSuspend'])->name('superadmin.merchants.suspend');
    Route::post('/merchants/{id}/activate', [SuperadminController::class, 'merchantActivate'])->name('superadmin.merchants.activate');
    Route::put('/merchants/{id}', [SuperadminController::class, 'merchantUpdate'])->name('superadmin.merchants.update');
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

// Brand Catalog Routes (superadmin CRUD, merchant admin/owner can edit)
Route::middleware(['auth', 'role:superadmin,admin,owner'])->prefix('admin/brand-catalog')->group(function () {
    Route::get('/', [BrandCatalogPhotoController::class, 'index'])->name('admin.brand-catalog.index');
    Route::get('/create', [BrandCatalogPhotoController::class, 'create'])->name('admin.brand-catalog.create');
    Route::post('/', [BrandCatalogPhotoController::class, 'store'])->name('admin.brand-catalog.store');
    Route::get('/{brandCatalogPhoto}/edit', [BrandCatalogPhotoController::class, 'edit'])->name('admin.brand-catalog.edit');
    Route::put('/{brandCatalogPhoto}', [BrandCatalogPhotoController::class, 'update'])->name('admin.brand-catalog.update');
    Route::delete('/{brandCatalogPhoto}', [BrandCatalogPhotoController::class, 'destroy'])->name('admin.brand-catalog.destroy');
    Route::post('/{brandCatalogPhoto}/toggle-active', [BrandCatalogPhotoController::class, 'toggleActive'])->name('admin.brand-catalog.toggle');
    Route::delete('/brand', [BrandCatalogPhotoController::class, 'destroyBrand'])->name('admin.brand-catalog.destroy-brand');
});

// Merchant Owner/Admin Elektronik Routes
Route::middleware(['auth', 'role:superadmin,admin,owner'])->prefix('owner')->group(function () {
    Route::get('/store', [MerchantProfileController::class, 'show'])->name('merchant.profile');
    Route::put('/store', [MerchantProfileController::class, 'update'])->name('merchant.profile.update');
    Route::post('/store/admins', [MerchantProfileController::class, 'storeAdmin'])->name('merchant.admin.store');
    Route::delete('/store/admins/{admin}', [MerchantProfileController::class, 'destroyAdmin'])->name('merchant.admin.destroy');
    Route::get('/elektronik/{type}', [ElektronikController::class, 'index'])->name('owner.elektronik.type');
    Route::get('/elektronik/{type}/create', [ElektronikController::class, 'create'])->name('owner.elektronik.create');
    Route::post('/elektronik/{type}', [ElektronikController::class, 'store'])->name('owner.elektronik.store');
    Route::get('/elektronik/{type}/{id}/edit', [ElektronikController::class, 'edit'])->name('owner.elektronik.edit');
    Route::put('/elektronik/{type}/{id}', [ElektronikController::class, 'update'])->name('owner.elektronik.update');
    Route::delete('/elektronik/{type}/{id}', [ElektronikController::class, 'destroy'])->name('owner.elektronik.destroy');
});

// Revenue (owner only, per kategori company)
Route::middleware(['auth', 'role:owner'])->prefix('owner/revenue')->group(function () {
    Route::get('/', [OwnerRevenueController::class, 'index'])->name('owner.revenue.index');
    Route::get('/create', [OwnerRevenueController::class, 'create'])->name('owner.revenue.create');
    Route::post('/', [OwnerRevenueController::class, 'store'])->name('owner.revenue.store');
    Route::get('/{invoice}', [OwnerRevenueController::class, 'show'])->name('owner.revenue.show');
    Route::delete('/{invoice}', [OwnerRevenueController::class, 'destroy'])->name('owner.revenue.destroy');
});

// Vehicles (Mobil) Routes
Route::middleware(['auth', 'role:superadmin,admin,owner'])->prefix('vehicles')->group(function () {
    Route::get('/', [VehicleWebController::class, 'index'])->name('vehicles.index');
    Route::get('/create', [VehicleWebController::class, 'create'])->name('vehicles.create');
    Route::post('/', [VehicleWebController::class, 'store'])->name('vehicles.store');
    Route::get('/{vehicle}/edit', [VehicleWebController::class, 'edit'])->name('vehicles.edit');
    Route::put('/{vehicle}', [VehicleWebController::class, 'update'])->name('vehicles.update');
    Route::delete('/{vehicle}', [VehicleWebController::class, 'destroy'])->name('vehicles.destroy');
});

// Motors Routes
Route::middleware(['auth', 'role:superadmin,admin,owner'])->prefix('motors')->group(function () {
    Route::get('/', [VehicleWebController::class, 'motor'])->name('motors.index');
});

Route::middleware('auth')->prefix('bookings')->group(function () {
    Route::get('/', [BookingWebController::class, 'index'])->name('bookings.index');
    Route::get('/create', [BookingWebController::class, 'create'])->name('bookings.create');
    Route::post('/', [BookingWebController::class, 'store'])->name('bookings.store');
    Route::get('/create-item/{type}/{item}', [BookingWebController::class, 'createItem'])->name('bookings.create-item');
    Route::post('/store-item/{type}', [BookingWebController::class, 'storeItem'])->name('bookings.store-item');
    Route::post('/store-multi', [BookingWebController::class, 'storeMulti'])->name('bookings.store-multi');
    Route::get('/manual-create', [BookingWebController::class, 'manualCreate'])->name('bookings.manual-create');
    Route::post('/manual-store', [BookingWebController::class, 'manualStore'])->name('bookings.manual-store');
    Route::post('/{booking}/replace-vehicle', [BookingWebController::class, 'replaceVehicle'])->name('bookings.replace-vehicle');
    Route::post('/{booking}/reschedule', [BookingWebController::class, 'reschedule'])->name('bookings.reschedule');
    Route::get('/{booking}', [BookingWebController::class, 'show'])->name('bookings.show');
    Route::post('/{booking}/ktp', [BookingWebController::class, 'uploadKtp'])->name('bookings.upload-ktp');
    Route::post('/{booking}/confirm', [BookingWebController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/{booking}/cancel', [BookingWebController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/{booking}/start-trip', [BookingWebController::class, 'startTrip'])->name('bookings.start');
    Route::post('/{booking}/complete', [BookingWebController::class, 'complete'])->name('bookings.complete');
    Route::post('/{booking}/assign-driver', [BookingWebController::class, 'assignDriver'])->name('bookings.assign-driver');
    Route::post('/{booking}/remove-driver', [BookingWebController::class, 'removeDriver'])->name('bookings.remove-driver');
});

Route::middleware(['auth', 'role:superadmin,owner,admin'])->prefix('drivers')->group(function () {
Route::get('/', [DriverWebController::class, 'index'])->name('drivers.index');
Route::get('/create', [DriverWebController::class, 'create'])->name('drivers.create');
Route::post('/', [DriverWebController::class, 'store'])->name('drivers.store');
Route::get('/{driver}', [DriverWebController::class, 'show'])->name('drivers.show');
Route::get('/{driver}/edit', [DriverWebController::class, 'edit'])->name('drivers.edit');
Route::put('/{driver}', [DriverWebController::class, 'update'])->name('drivers.update');
Route::delete('/{driver}', [DriverWebController::class, 'destroy'])->name('drivers.destroy');
});

// Inspector Account Routes
Route::middleware(['auth', 'role:superadmin,owner'])->prefix('inspectors')->group(function () {
    Route::get('/', [InspectorWebController::class, 'index'])->name('inspectors.index');
    Route::get('/create', [InspectorWebController::class, 'create'])->name('inspectors.create');
    Route::post('/', [InspectorWebController::class, 'store'])->name('inspectors.store');
    Route::get('/{inspector}/edit', [InspectorWebController::class, 'edit'])->name('inspectors.edit');
    Route::put('/{inspector}', [InspectorWebController::class, 'update'])->name('inspectors.update');
    Route::delete('/{inspector}', [InspectorWebController::class, 'destroy'])->name('inspectors.destroy');
});

// Attendance Routes
Route::middleware('auth')->prefix('attendance')->group(function () {
    Route::get('/', [AttendanceWebController::class, 'index'])->name('attendance.index');
    Route::post('/check-in', [AttendanceWebController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/check-out', [AttendanceWebController::class, 'checkOut'])->name('attendance.check-out');
});

Route::middleware('auth')->prefix('invoices')->group(function () {
    Route::get('/', [InvoiceWebController::class, 'index'])->name('invoices.index');
    Route::get('/create', [InvoiceWebController::class, 'create'])->name('invoices.create');
    Route::post('/', [InvoiceWebController::class, 'store'])->name('invoices.store');
    Route::get('/{invoice}/print', [InvoiceWebController::class, 'print'])->name('invoices.print');
    Route::post('/{invoice}/send', [InvoiceWebController::class, 'send'])->name('invoices.send');
    Route::get('/{invoice}', [InvoiceWebController::class, 'show'])->name('invoices.show');
    Route::post('/{invoice}/pay', [InvoiceWebController::class, 'pay'])->name('invoices.pay');
    Route::post('/payments/{payment}/verify', [InvoiceWebController::class, 'verifyPayment'])->name('invoices.verify-payment');
    Route::post('/payments/{payment}/reject', [InvoiceWebController::class, 'rejectPayment'])->name('invoices.reject-payment');
});

Route::middleware('auth')->prefix('inspections')->group(function () {
    Route::get('/', [InspectionWebController::class, 'index'])->name('inspections.index');
    Route::get('/create', [InspectionWebController::class, 'create'])->name('inspections.create');
    Route::post('/', [InspectionWebController::class, 'store'])->name('inspections.store');
    Route::post('/{inspection}/start', [InspectionWebController::class, 'start'])->name('inspections.start');
    Route::post('/{inspection}/complete', [InspectionWebController::class, 'complete'])->name('inspections.complete');
    Route::get('/{inspection}', [InspectionWebController::class, 'show'])->name('inspections.show');
});

Route::middleware(['auth', 'role:driver'])->prefix('driver-report')->group(function () {
    Route::get('/', [InspectionWebController::class, 'reportForm'])->name('driver.report');
    Route::post('/', [InspectionWebController::class, 'reportStore'])->name('driver.report.store');
});

Route::middleware('auth')->prefix('trip-reports')->group(function () {
    Route::get('/', [TripReportWebController::class, 'index'])->name('reports.index');
    Route::get('/create', [TripReportWebController::class, 'create'])->name('reports.create');
    Route::post('/', [TripReportWebController::class, 'store'])->name('reports.store');
    Route::get('/{tripReport}', [TripReportWebController::class, 'show'])->name('reports.show');
});

Route::middleware(['auth', 'role:superadmin,admin,owner'])->prefix('salaries')->group(function () {
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
    Route::get('/{replacement}', [ReplacementWebController::class, 'show'])->name('replacements.show');
    Route::post('/{replacement}/approve', [ReplacementWebController::class, 'approve'])->name('replacements.approve');
    Route::post('/{replacement}/reject', [ReplacementWebController::class, 'reject'])->name('replacements.reject');
    Route::post('/{replacement}/update-status', [ReplacementWebController::class, 'updateStatus'])->name('replacements.update-status');
});

// Item Replacement Routes (Electronics)
Route::middleware('auth')->prefix('item-replacements')->group(function () {
    Route::get('/', [ItemReplacementWebController::class, 'index'])->name('item-replacements.index');
    Route::get('/create', [ItemReplacementWebController::class, 'create'])->name('item-replacements.create');
    Route::post('/', [ItemReplacementWebController::class, 'store'])->name('item-replacements.store');
    Route::get('/{replacement}', [ItemReplacementWebController::class, 'show'])->name('item-replacements.show');
    Route::post('/{replacement}/approve', [ItemReplacementWebController::class, 'approve'])->name('item-replacements.approve');
    Route::post('/{replacement}/reject', [ItemReplacementWebController::class, 'reject'])->name('item-replacements.reject');
    Route::post('/{replacement}/return', [ItemReplacementWebController::class, 'returnItem'])->name('item-replacements.return');
});

// Rentals Routes
Route::middleware('auth')->prefix('rentals')->group(function () {
    Route::get('/', [RentalController::class, 'index'])->name('rentals.index');
    Route::get('/create', [RentalController::class, 'create'])->name('rentals.create');
    Route::post('/', [RentalController::class, 'store'])->name('rentals.store');
    Route::get('/{rental}/edit', [RentalController::class, 'edit'])->name('rentals.edit');
    Route::put('/{rental}', [RentalController::class, 'update'])->name('rentals.update');
    Route::get('/{rental}', [RentalController::class, 'show'])->name('rentals.show');
    Route::post('/{rental}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');
    Route::post('/{rental}/confirm', [RentalController::class, 'confirm'])->name('rentals.confirm');
    Route::post('/{rental}/complete', [RentalController::class, 'complete'])->name('rentals.complete');
});

// Maintenance Routes (superadmin, merchant admin/owner, dan inspector merchant)
Route::middleware(['auth', 'role:superadmin,admin,owner,inspector'])->prefix('maintenances')->group(function () {
    Route::get('/', [MaintenanceController::class, 'index'])->name('maintenances.index');
    Route::post('/', [MaintenanceController::class, 'store'])->name('maintenances.store');
    Route::put('/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenances.update');
    Route::delete('/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenances.destroy');
});
