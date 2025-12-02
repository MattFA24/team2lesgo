<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // We pass empty data so the page loads without database errors
    return view('ai-app', ['savedTexts' => []]);
});

// buat nanti ini JANGAN DIHAPUS
use App\Http\Controllers\AiController;
Route::post('/rewrite', [AiController::class, 'rewrite']);
Route::post('/save', [AiController::class, 'save'])->name('save');
Route::delete('/delete/{id}', [AiController::class, 'destroy'])->name('delete');