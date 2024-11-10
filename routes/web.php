<?php

use App\Http\Controllers\User\UserOrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\User\UserLandingController;


//Landing Page Routes
Route::get('/', [UserLandingController::class, 'index'])->name('home');
Route::get('/cart', function () {
    return view('landing.shopping-cart');
})->name('cart');

Route::get('/orders', [UserOrderController::class, 'index'])->name('user.orders');

//Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');



// Admin Routes
Route::prefix('/admin/dashboard')->group(
    function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    }
);

Route::prefix('/admin/categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
});
Route::prefix('/admin/products')->group(function () {
    Route::get('/', [ProductController::class, "index"])->name('admin.products.index');
    Route::post('/', [ProductController::class, "store"])->name('admin.products.store');
    Route::put('/{product}', [ProductController::class, "update"])->name('admin.products.update');
    Route::delete('/{product}', [ProductController::class, "destroy"])->name('admin.products.destroy');
});

Route::prefix('/admin/users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
});
Route::prefix('admin/orders')->group(function () {
    Route::get('/', [OrdersController::class, 'index'])->name('admin.orders.index');
    Route::patch('/{order}/status', [OrdersController::class, 'updateStatus'])->name('admin.orders.update-status');
});

Route::get('/admin/history-order', [OrderHistoryController::class, 'index'])->name('admin.history.index');
