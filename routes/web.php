<?php

use App\Http\Controllers\AiController;
use Illuminate\Support\Facades\Route;

// 1. Show the App (fetches saved history from DB)
Route::get('/', [AiController::class, 'index']);

// 2. Rewrite Text (calls Gemini API)
Route::post('/rewrite', [AiController::class, 'rewrite']);

// 3. Save to Database
Route::post('/save', [AiController::class, 'save'])->name('save');

// 4. Delete from Database
Route::delete('/delete/{id}', [AiController::class, 'destroy'])->name('delete');