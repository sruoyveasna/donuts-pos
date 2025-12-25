<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| JSON auth (session-based; uses "web" middleware & CSRF)
|--------------------------------------------------------------------------
*/
Route::prefix('api/auth')->group(function () {
    Route::middleware('guest')->post('register', [AuthController::class, 'register']);
    Route::middleware('guest')->post('login',    [AuthController::class, 'login']);

    Route::middleware('auth')->post('logout',    [AuthController::class, 'logout']);
    Route::middleware('auth')->get('me',         [AuthController::class, 'me']);
});

/*
|--------------------------------------------------------------------------
| Guest pages (no sidebar)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::view('/login',    'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
});

/*
|--------------------------------------------------------------------------
| App pages (with sidebar layout)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Main
    Route::view('/customers', 'customers.index')->name('customers');
    Route::view('/dashboard', 'dashboard.index')->name('dashboard');

    // Index pages
    Route::view('/categories',  'categories.index')->name('categories');
    Route::view('/discounts',   'discounts.index')->name('discounts'); // ✅ added
    Route::view('/menu',        'menu.index')->name('menu');
    Route::view('/orders',      'orders.index')->name('orders');
    Route::view('/ingredients', 'ingredients.index')->name('ingredients');

    // Detail pages
    Route::view('/orders/{order}',    'orders.show')->name('orders.show');
    Route::view('/ingredients/{id}',  'ingredients.show')->name('ingredients.show');

    // POS
    Route::view('/pos', 'pos.index')->name('pos');

    // Users
    Route::get('/users', [UserController::class, 'page'])->name('users');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/recipes', fn() => view('recipes.index'))->name('recipes.index');
    Route::get('/recipes/{menuItemId}', fn($menuItemId) => view('recipes.show', compact('menuItemId')))->name('recipes.show');

});

/*
|--------------------------------------------------------------------------
| Root redirect
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Misc
|--------------------------------------------------------------------------
*/
Route::view('/document-library', 'document-library');

Route::get('/locale/{locale}', LocaleController::class)
    ->name('locale.switch');
