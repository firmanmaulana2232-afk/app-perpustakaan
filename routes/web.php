<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Resource Routes Praktikum
Route::resource('books', BookController::class);
Route::resource('categories', CategoryController::class)->except(['show']);
Route::resource('members', MemberController::class);
Route::resource('loans', LoanController::class);

// Custom Route Pengembalian Buku
Route::put('/loans/{id}/kembalikan', [LoanController::class, 'kembalikan'])
    ->name('loans.kembalikan');

// Route Group Tugas (/admin)
Route::prefix('admin')->group(function () {
    Route::get('/info', function () {
        return 'Informasi Sistem Perpustakaan - Akses Panel Administrator';
    })->name('admin.info');
});