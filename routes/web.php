<?php

use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- THIS IS THE BLOCK YOU NEED TO ADD ---
// It should be inside your routes/web.php file
Route::middleware('auth')->group(function () {
    Route::get('/requisitions/{requisition}/print', [PrintController::class, 'printRequisition'])->name('requisitions.print');
});
