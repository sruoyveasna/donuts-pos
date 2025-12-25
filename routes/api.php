<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuItemVariantController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\AuthController;
Route::post('/auth/register', [AuthController::class, 'register']);          // send OTP for registration
Route::post('/auth/forgot', [AuthController::class, 'forgot']);              // send OTP for reset
Route::post('/auth/otp/resend', [AuthController::class, 'resendOtp']);        // resend
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);        // verify
Route::post('/auth/password/otp', [AuthController::class, 'resetWithOtp']);   // set/reset password with OTP

Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/auth/me', [AuthController::class, 'me']);
/**
 * Categories (you already have these)
 */
// Public (read)
Route::apiResource('categories', CategoryController::class)->only(['index','show']);

// Protected (write)
Route::middleware(['auth:sanctum', 'ensure.role:Super Admin,Admin'])->group(function () {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    Route::post('categories/{id}/restore', [CategoryController::class, 'restore']);
});

/**
 * Menu Items
 */
// Public (read)
Route::get('menu/items',                [MenuItemController::class, 'index']);
Route::get('menu/items/search',         [MenuItemController::class, 'search']);
Route::get('menu/items/{menuItem}',     [MenuItemController::class, 'show']);
// Variants read (for sizes list)
Route::get('menu/items/{menuItem}/variants', [MenuItemVariantController::class, 'index']);

// Protected (write)
Route::middleware(['auth:sanctum', 'ensure.role:Super Admin,Admin'])->group(function () {
    // Items
    Route::post('menu/items',                     [MenuItemController::class, 'store']);
    Route::put('menu/items/{menuItem}',           [MenuItemController::class, 'update']);
    Route::patch('menu/items/{menuItem}',         [MenuItemController::class, 'update']);
    Route::delete('menu/items/{menuItem}',        [MenuItemController::class, 'destroy']);
    Route::post('menu/items/{menuItem}/restore',  [MenuItemController::class, 'restore']);

    // Variants (create under item; shallow update/delete)
    Route::post('menu/items/{menuItem}/variants',                       [MenuItemVariantController::class, 'store']);
    Route::patch('variants/{variant}',                                   [MenuItemVariantController::class, 'update']);
    Route::delete('variants/{variant}',                                  [MenuItemVariantController::class, 'destroy']);
    Route::post('menu/items/{menuItem}/variants/{variant}/restore',      [MenuItemVariantController::class, 'restore']);

    Route::post('/settings', [SettingsController::class, 'update']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pos/orders', [OrderController::class, 'store']);
    Route::post('/pos/orders/{order}/payments', [PaymentController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/pos/discounts/preview', [OrderController::class, 'previewDiscount']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/restore', [UserController::class, 'restore']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/ingredients', [\App\Http\Controllers\IngredientController::class, 'index']);
    Route::post('/ingredients', [\App\Http\Controllers\IngredientController::class, 'store']);
    Route::get('/ingredients/{ingredient}', [\App\Http\Controllers\IngredientController::class, 'show']);
    Route::patch('/ingredients/{ingredient}', [\App\Http\Controllers\IngredientController::class, 'update']);
    Route::delete('/ingredients/{ingredient}', [\App\Http\Controllers\IngredientController::class, 'destroy']);

    Route::post('/ingredients/{ingredient}/adjust', [\App\Http\Controllers\IngredientController::class, 'adjust']);
});


Route::get('/discounts', [DiscountController::class, 'index']);
Route::get('/discounts/{discount}', [DiscountController::class, 'show']);


Route::middleware(['auth:sanctum', 'ensure.role:Super Admin,Admin'])->group(function () {
    Route::post('/discounts', [DiscountController::class, 'store']);
    Route::patch('/discounts/{discount}', [DiscountController::class, 'update']);
    Route::delete('/discounts/{discount}', [DiscountController::class, 'destroy']);
    Route::post('/discounts/{id}/restore', [DiscountController::class, 'restore']);
});


Route::get('/recipes', [RecipeController::class, 'index']);                       // list lines (optional filters)
Route::get('/recipes/group', [RecipeController::class, 'showGroup']);            // fetch whole recipe group
Route::put('/recipes/group', [RecipeController::class, 'upsertGroup']);          // replace/upsert whole group
Route::delete('/recipes/group', [RecipeController::class, 'deleteGroup']);       // delete whole group

Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);        // delete single line
