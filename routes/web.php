<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClockInController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRoleEnum;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === UserRoleEnum::ADMIN) {
            return redirect()->route('admin.users');
        }
        return redirect()->route('clock-in.index');
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/registers', [ClockInController::class, 'registers'])->name('admin.registers');
        Route::get('/users/search-cep', [UserController::class, 'searchCep'])->name('admin.users.search-cep');
    });

    Route::middleware('employee')->group(function () {
        Route::get('/clock-in', [ClockInController::class, 'index'])->name('clock-in.index');
        Route::post('/clock-in', [ClockInController::class, 'store'])->name('clock-in.store');
    });
});

require __DIR__ . '/auth.php';
