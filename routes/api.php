<?php

use App\Http\Controllers\Api\V1\MurfFalconApiController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\PromptGenerationController;
use App\Http\Controllers\Api\V1\TranslationController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::apiResource('posts', PostController::class);
        Route::get('users', [UserController::class, 'index']);

        Route::apiResource('prompt-generations', PromptGenerationController::class)
            ->only(['index', 'store']);

        Route::post('translations', [TranslationController::class, 'store'])->name('translations.store');
        Route::get('tts/stream', [MurfFalconApiController::class, 'streamTTS'])->name('tts.stream');
        Route::get('speech-voices', [MurfFalconApiController::class, 'getSpeechVoice'])->name('speech.voices');
    });
});

require __DIR__.'/auth.php';
