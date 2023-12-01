<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use App\Http\Controllers\StoryRequestController;

// Route to show the form
Route::get('/story-request', [StoryRequestController::class, 'create']);

// Route to handle form submission
Route::post('/story-request', [StoryRequestController::class, 'store']);

Route::get('/generate-pdf-form', [PdfController::class, 'showForm']);
Route::post('/generate-pdf', [PdfController::class, 'generatePdf']);

