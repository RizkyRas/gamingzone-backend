<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CafeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| GAMING ZONE REST API ROUTES
|--------------------------------------------------------------------------
*/

// Public Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Rooms & Menus Catalog
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);
Route::get('/menus', [CafeController::class, 'getMenus']);

// Midtrans Payment APIs
Route::post('/payments/create-transaction', [PaymentController::class, 'createTransaction']);
Route::post('/payments/webhook', [PaymentController::class, 'handleWebhook']);

// Protected & Customer/Admin Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);

    // Orders Cafe
    Route::get('/orders', [CafeController::class, 'getOrders']);
    Route::post('/orders', [CafeController::class, 'storeOrder']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

// Fallback guest bookings & orders (if user not logged in via bearer token)
Route::post('/public/bookings', [BookingController::class, 'store']);
Route::get('/public/orders', [CafeController::class, 'getOrders']);
Route::post('/public/orders', [CafeController::class, 'storeOrder']);
Route::get('/public/notifications', [NotificationController::class, 'index']);

// Users Management APIs
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// QA & Testing Endpoint for 500 Internal Server Error
Route::get('/test-500', function () {
    return response()->json([
        'success' => false,
        'message' => 'Simulasi Internal Server Error 500 untuk Pengujian REST API GamingZone'
    ], 500);
});


