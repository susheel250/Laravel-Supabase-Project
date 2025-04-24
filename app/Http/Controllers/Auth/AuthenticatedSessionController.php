<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {

        // Logic for email/password login
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // Email login successful
            session()->forget('supabase_token'); // Clear GitHub session if it exists
            return redirect()->route('dashboard');
        }
        // Logic for GitHub login
        if (session('supabase_token')) {
            return redirect()->route('github.dashboard');
        }

        return redirect()->route('login')->withErrors(['email' => 'Invalid credentials']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        session()->forget('supabase_token');
        session()->forget('supabase_user');
        Auth::guard('web')->logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
