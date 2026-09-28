<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [JobController::class, 'index'])->name('home');
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/notifications/read', [DashboardController::class, 'read'])->name('notifications.read');
    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
    Route::post('/jobs/{job}/apply', [ApplicationController::class, 'store'])->name('jobs.apply');
    Route::post('/jobs/{job}/bookmark', [ApplicationController::class, 'bookmark'])->name('jobs.bookmark');
    Route::delete('/jobs/{job}/bookmark', [ApplicationController::class, 'unsave'])->name('jobs.unsave');
    Route::get('/jobs/{job}/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{application}', [ApplicationController::class, 'update'])->name('applications.update');
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::put('/admin/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::patch('/admin/users/{user}/role', [AdminController::class, 'role'])->name('users.role');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    Route::patch('/admin/jobs/{job}/feature', [AdminController::class, 'feature'])->name('jobs.feature');
});
Route::get('/jobs/{job}',[JobController::class, 'show'])->name('jobs.show');

Route::get('/companies/{user}',[ProfileController::class,'company'])->name('companies.show');
Route::get('/profiles/{user}/resume',[ProfileController::class,'resume'])->middleware('auth')->name('resume.download');
Route::delete('/profile/resume',[ProfileController::class,'removeResume'])->middleware('auth')->name('resume.destroy');
