<?php

use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\OrderAdminController;

Auth::routes(['register' => false]);

Route::get('/', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->middleware('auth')->name('home');
Route::prefix('admin')->middleware(['auth','admin'])->group(function() {

    Route::any('/dashboard', [App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('dashboard');

    // Admins routes
    Route::prefix('admins')->group(function () {
        Route::any('/', [App\Http\Controllers\Dashboard\AdminController::class, 'index'])->name('admins');
        Route::any('/add', [App\Http\Controllers\Dashboard\AdminController::class, 'add'])->name('add-admin');
        Route::any('/edit/{id}', [App\Http\Controllers\Dashboard\AdminController::class, 'edit'])->name('edit-admin');
        Route::any('/delete/{id}', [App\Http\Controllers\Dashboard\AdminController::class, 'delete'])->name('delete-admin');
    });

    // Employees routes
    Route::prefix('employees')->group(function () {
        Route::any('/', [App\Http\Controllers\Dashboard\EmployeeController::class, 'index'])->name('employees');
        Route::any('/add', [App\Http\Controllers\Dashboard\EmployeeController::class, 'add'])->name('add-employee');
        Route::any('/edit/{id}', [App\Http\Controllers\Dashboard\EmployeeController::class, 'edit'])->name('edit-employee');
        Route::any('/delete/{id}', [App\Http\Controllers\Dashboard\EmployeeController::class, 'delete'])->name('delete-employee');
    });

    // Users routes
    Route::prefix('users')->group(function () {
        Route::any('/', [App\Http\Controllers\Dashboard\UserController::class, 'index'])->name('users');
        Route::any('/add', [App\Http\Controllers\Dashboard\UserController::class, 'add'])->name('add-user');
        Route::any('/edit/{id}', [App\Http\Controllers\Dashboard\UserController::class, 'edit'])->name('edit-user');
        Route::any('/delete/{id}', [App\Http\Controllers\Dashboard\UserController::class, 'delete'])->name('delete-user');
    });


    // Category routes
    Route::prefix('categories')->group(function(){
        Route::any('/', [App\Http\Controllers\Dashboard\CategoryController::class, 'index'])->name('be-categories');
        Route::any('/add', [App\Http\Controllers\Dashboard\CategoryController::class, 'add'])->name('add-category');
        Route::any('/edit/{id}', [App\Http\Controllers\Dashboard\CategoryController::class, 'edit'])->name('edit-category');
        Route::any('/delete/{id}', [App\Http\Controllers\Dashboard\CategoryController::class, 'delete'])->name('delete-category');
    });


    // Products routes
    Route::prefix('products')->group(function () {
        Route::any('/', [App\Http\Controllers\Dashboard\ProductController::class, 'index'])->name('products');
        Route::any('/add', [App\Http\Controllers\Dashboard\ProductController::class, 'add'])->name('add-product');
        Route::any('/edit/{id}', [App\Http\Controllers\Dashboard\ProductController::class, 'edit'])->name('edit-product');
        Route::any('/delete/{id}', [App\Http\Controllers\Dashboard\ProductController::class, 'delete'])->name('delete-product');
    });

    // Orders routes
    Route::prefix('orders')->group(function(){
        Route::any('/', [App\Http\Controllers\Dashboard\OrderController::class, 'index'])->name('orders');
        Route::any('/edit/{id}', [App\Http\Controllers\Dashboard\OrderController::class, 'edit'])->name('edit-order');
        Route::any('/delete/{id}', [App\Http\Controllers\Dashboard\OrderController::class, 'delete'])->name('delete-order');
    });
});
