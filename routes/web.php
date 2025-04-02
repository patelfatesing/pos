<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Models\User;


Route::middleware(['role:admin'])->get('/admin-dashboard', function () {
    return 'Admin Dashboard';
});

Route::middleware(['permission:editor_permission'])->get('/editor-dashboard', function () {
    return 'Editor Dashboard';
});
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified','role:admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/users/list', [UserController::class, 'index'])->name('users.list');
    Route::post('/users/get-data', [UserController::class, 'getData'])->name('users.getData');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/update', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/roles/list', [RolesController::class, 'index'])->name('roles.list');
    Route::post('/roles/get-data', [RolesController::class, 'getData'])->name('roles.getData');
    Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    Route::post('/roles/{id}', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');

    Route::get('/store/list', [BranchController::class, 'index'])->name('branch.list');
    Route::post('/store/get-data', [BranchController::class, 'getData'])->name('user.getData');
    Route::get('/store/create', [BranchController::class, 'create'])->name('branch.create');
    Route::post('/store/store', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/store/edit/{id}', [BranchController::class, 'edit'])->name('branch.edit');
    Route::put('/store/{id}', [BranchController::class, 'update'])->name('branch.update');
    Route::delete('/store/delete/{id}', [BranchController::class, 'destroy'])->name('branch.destroy');
});

require __DIR__.'/auth.php';
