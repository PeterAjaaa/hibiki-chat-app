<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController,
    ConversationController,
    MessageController
};

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('tokens/refresh', [AuthController::class, 'refreshAccessToken']);


    Route::post('conversations', [ConversationController::class, 'createNewConversation']);
    Route::post('conversations/{id}/messages', [MessageController::class, 'sendMessage']);

    Route::get('conversations', [ConversationController::class, 'getAllConversation']);
    Route::get('conversations/{id}', [ConversationController::class, 'getAllMessagesFromConversation']);

    Route::post('conversations/{id}/participants', [ConversationController::class, 'addParticipant']);
    Route::delete('conversations/{id}/participants', [ConversationController::class, 'removeParticipant']);

    Route::delete('conversations/{id}', [ConversationController::class, 'destroyConversation']);
});
