<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupabaseAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Default home route
Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/**
 * Step 1: Redirect to Supabase GitHub Login
 */
Route::get('/auth/redirect', [SupabaseAuthController::class, 'redirectToProvider']);



/**
 * Step 2: Supabase Redirect Callback
 */
Route::get('/post-login', [SupabaseAuthController::class, 'handleProviderCallback']);


/**
 * Step 3: Supabase-protected Dashboard
 */
Route::get('/dashboard', function () {
    // Check if the user is logged in via email/password (use Auth)
    if (Auth::check()) {
        // If the user is logged in via email/password, show the dashboard
        return view('dashboard');
    }

    // If not logged in and no supabase_token, redirect to GitHub login
    if (!session('supabase_token')) {
        return redirect('/auth/redirect');
    }

    // If the supabase_token exists, show the dashboard
    return view('dashboard');
})->name('dashboard');


Route::get('/handle-supabase', function () {
    return view('handle-supabase');
});


// Profile section - optional if you plan to use Laravel's auth system
/*
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/

// If you're NOT using Laravel Breeze/Fortify auth, remove this line:
 require __DIR__.'/auth.php';
