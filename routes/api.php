<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('conversations', [ConversationController::class, 'index']);
    Route::post('conversations', [ConversationController::class, 'create']);
    Route::get('conversations/{id}', [ConversationController::class, 'show']);
    Route::post('conversations/{id}/participants', [ConversationController::class, 'addParticipant']);
    Route::delete('conversations/{id}/participants', [ConversationController::class, 'removeParticipant']);
    Route::delete('conversations/{id}', [ConversationController::class, 'destroy']);
});
