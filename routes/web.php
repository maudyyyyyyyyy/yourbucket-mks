<?php

use App\Http\Controllers\OrderHistoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrdersController;


//Landing Page Routes
Route::get('/', function () {
    return view('landing.landing-page');
})->name('home');

Route::get('/cart', function () {
    return view('landing.shopping-cart');
})->name('cart');

//Auth Routes
Route::get('/auth/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/auth/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/search', function () {
    return view('search');
});
Route::get('/products', function () {
    return view('products');
});

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
