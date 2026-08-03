<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirigir raíz al login
Route::get('/', fn () => redirect()->route('login'));

// Dashboard principal (todos los usuarios autenticados)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Perfil del usuario
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Gestión de usuarios — solo admin
Route::middleware(['auth', 'role:admin'])->prefix('usuarios')->name('usuarios.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/papelera', [UserController::class, 'trashed'])->name('trashed');
    Route::get('/crear', [UserController::class, 'create'])->name('create');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{user}/editar', [UserController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update');
    Route::patch('/{user}/activar', [UserController::class, 'toggleActive'])->name('toggle-active');
    Route::patch('/{id}/restaurar', [UserController::class, 'restore'])->name('restore');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    Route::delete('/{id}/definitivo', [UserController::class, 'forceDelete'])->name('force-delete');
});

require __DIR__.'/auth.php';

