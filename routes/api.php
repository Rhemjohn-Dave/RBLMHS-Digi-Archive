<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DownloadLogController;
use App\Http\Controllers\Api\DownloadPermissionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ResearchController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', [\Illuminate\Broadcasting\BroadcastController::class, 'authenticate']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Routes that require approved status (students/faculty)
    Route::middleware('approved')->group(function () {
    Route::get('/research', [ResearchController::class, 'index']);
    Route::get('/research/{research}', [ResearchController::class, 'show']);
    Route::get('/research/{research}/download', [ResearchController::class, 'download']);

    // Request download permission (student)
    Route::post('/research/{research}/request-download', [DownloadPermissionController::class, 'requestDownload']);

    // Faculty: upload & my submissions
    Route::middleware('faculty')->group(function () {
        Route::post('/research', [ResearchController::class, 'store']);
        Route::get('/my-submissions', [ResearchController::class, 'mySubmissions']);
    });

    // Admin only
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/{user}/approve', [UserController::class, 'approve']);
        Route::post('/users/{user}/reject', [UserController::class, 'reject']);

        Route::get('/research-pending', [ResearchController::class, 'pending']);
        Route::get('/research-published', [ResearchController::class, 'published']);
        Route::put('/research/{research}', [ResearchController::class, 'update']);
        Route::post('/research/{research}/approve', [ResearchController::class, 'approve']);
        Route::post('/research/{research}/reject', [ResearchController::class, 'reject']);

        Route::get('/download-requests', [DownloadPermissionController::class, 'index']);
        Route::post('/download-permissions/{permission}/approve', [DownloadPermissionController::class, 'approve']);
        Route::post('/download-permissions/{permission}/reject', [DownloadPermissionController::class, 'reject']);

        Route::get('/download-logs', [DownloadLogController::class, 'index']);
        Route::get('/download-logs/stats', [DownloadLogController::class, 'stats']);
    });
    });
});
