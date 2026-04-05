<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderHistoryController;

use App\Http\Controllers\User\UserCartController;
use App\Http\Controllers\User\UserOrderController;
use App\Http\Controllers\User\UserLandingController;
use App\Http\Controllers\User\UserCheckoutController;
use App\Http\Controllers\User\ProductDetailsController;

use App\Http\Controllers\Admin\OrderController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [UserLandingController::class, 'index'])->name('home');

Route::get('/detail/{slug}', [ProductDetailsController::class, 'index'])->name('detail');

Route::get('/cart', [UserCartController::class, 'index'])->name('cart');

Route::post('/add-to-cart/{id}', [ProductDetailsController::class,'addToCart'])->name('add.cart');


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');

    /*
    |--------------------------------------------------------------------------
    | Forgot Password
    |--------------------------------------------------------------------------
    */

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {

        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);

    })->name('password.email');


    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');


    Route::post('/reset-password', function (Request $request) {

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {

                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil direset!')
            : back()->withErrors(['email' => [__($status)]]);

    })->name('password.update');

});


Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->name('auth.logout')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| Payment Callback
|--------------------------------------------------------------------------
*/

Route::post('/payments/midtrans-callback', [UserCheckoutController::class, 'callback'])
    ->name('midtrans.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/orders', [UserOrderController::class, 'index'])
        ->name('user.orders');

    Route::post('/checkout/process', [UserCheckoutController::class, 'process'])
        ->name('checkout.process');

    Route::post('/payments/update-status', [UserCheckoutController::class, 'updateStatus'])
        ->name('payment.update-status');

});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','role:admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    /*
    | Categories
    */

    Route::prefix('categories')->group(function () {

        Route::get('/', [CategoryController::class, 'index'])
            ->name('admin.categories.index');

        Route::post('/', [CategoryController::class, 'store'])
            ->name('admin.categories.store');

        Route::put('/{category}', [CategoryController::class, 'update'])
            ->name('admin.categories.update');

        Route::delete('/{category}', [CategoryController::class, 'destroy'])
            ->name('admin.categories.destroy');

    });

    /*
    | Products
    */

    Route::prefix('products')->group(function () {

        Route::get('/', [ProductController::class, 'index'])
            ->name('admin.products.index');

        Route::post('/', [ProductController::class, 'store'])
            ->name('admin.products.store');

        Route::put('/{product}', [ProductController::class, 'update'])
            ->name('admin.products.update');

        Route::delete('/{product}', [ProductController::class, 'destroy'])
            ->name('admin.products.destroy');

    });

    /*
    | Users
    */

    Route::prefix('users')->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->name('admin.users.index');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->name('admin.users.destroy');

    });

    /*
    | Orders
    */

    Route::prefix('orders')->group(function () {

        Route::get('/', [OrdersController::class, 'index'])
            ->name('admin.orders.index');

        Route::patch('/{order}/status', [OrdersController::class, 'updateStatus'])
            ->name('admin.orders.update-status');

    });

    /*
    | History
    */

    Route::get('/history-order', [OrderHistoryController::class, 'index'])
        ->name('admin.history.index');

});


/*
|--------------------------------------------------------------------------
| Export Sales
|--------------------------------------------------------------------------
*/

Route::get('/admin/export-daily-sales', [OrderController::class, 'exportDailySales'])
    ->name('admin.export.daily.sales');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('admin.dashboard');

// Tambahkan 2 baris ini:
Route::get('/sales', [DashboardController::class, 'sales'])
    ->name('admin.sales');

Route::get('/dashboard/sales-chart', [DashboardController::class, 'salesChart'])
    ->name('admin.dashboard.sales-chart');

Route::get('/sales/export', [App\Http\Controllers\SalesExportController::class, 'export'])
    ->name('admin.sales.export');
    
// Tambah di bawah Route::get('/cart', ...)
Route::get('/api/products/stock', function () {
    $ids = request('ids', []);
    $products = \App\Models\Product::whereIn('id', $ids)->get(['id', 'stock', 'name']);
    return response()->json($products);
})->name('api.products.stock');