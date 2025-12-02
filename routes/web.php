<?php

use App\Http\Controllers\AiController;
use Illuminate\Support\Facades\Route;

// This tells Laravel to use your new AiController instead of the default view
Route::get('/', [AiController::class, 'index']);
Route::post('/rewrite', [AiController::class, 'rewrite']);
Route::post('/save', [AiController::class, 'save'])->name('save');
Route::delete('/delete/{id}', [AiController::class, 'destroy'])->name('delete');