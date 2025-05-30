<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use App\Helpers\SupabaseHelper;

class SupabaseAuthController extends Controller
{
    public function redirectToProvider()
    {
        session()->forget(['supabase_token', 'supabase_user']);

        $redirect = urlencode('http://localhost:8000/handle-supabase');
        $supabaseUrl = 'https://hxqpwmdypzscudyoqehk.supabase.co/auth/v1/authorize';
        $provider = 'github';

        // 👇 Add `prompt=login` to force re-authentication
        return redirect("{$supabaseUrl}?provider={$provider}&redirect_to={$redirect}&prompt=login");
    }


    public function handleProviderCallback(Request $request)
    {
        $accessToken = $request->query('access_token');

        if (!$accessToken) {
            return redirect('/')->with('error', 'No token provided');
        }

        session(['supabase_token' => $accessToken]);

        $userInfo = SupabaseHelper::getUserInfo($accessToken);

        if (!$userInfo || !isset($userInfo['email'])) {
            \Log::error('Invalid user info from Supabase:', $userInfo ?? []);
            return redirect('/')->with('error', 'Invalid user data from Supabase');
        }

        \Log::info('Full Supabase userInfo:', $userInfo);


        // Create or update the user
        $user = User::firstOrNew(['email' => $userInfo['email']]);

        // Update only if not already set or set to a default placeholder
        if (!$user->name || $user->name === 'GitHub User') {
            $user->name = $userInfo['user_metadata']['preferred_username'] ?? 'GitHub User';
        }

        $user->provider = $userInfo['app_metadata']['provider'] ?? 'github';
        $user->provider_id = $userInfo['user_metadata']['provider_id'] ?? null;
        $user->avatar_url = $userInfo['user_metadata']['avatar_url'] ?? null;

        $user->save();




        // Laravel login
        Auth::login($user);

        return redirect('/dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('status', 'Logged out successfully');
    }

    // Redirect to Google for OAuth
    public function redirectToGoogle()
    {
        $supabaseUrl = 'https://hxqpwmdypzscudyoqehk.supabase.co/auth/v1/authorize';
        $redirectUri = route('google.callback');
        $provider = 'google';

        // \Log::info('Redirecting to Google via Supabase', [
        //     'url' => "{$supabaseUrl}?provider={$provider}&redirect_to={$redirectUri}"
        // ]);

        return redirect("{$supabaseUrl}?provider={$provider}&redirect_to={$redirectUri}");
    }


    // Handle Google OAuth callback
    public function handleGoogleCallback(Request $request)
    {
        // \Log::info("Google callback hit");

        $accessToken = $request->input('access_token');
        // \Log::info("Access token received: " . json_encode(['token' => $accessToken]));

        if (!$accessToken) {
            return redirect('/')->with('error', 'No token provided');
        }

        // Get user from Supabase
        $userInfo = SupabaseHelper::getUserInfo($accessToken);

        if (!$userInfo || !isset($userInfo['email'])) {
            return redirect('/')->with('error', 'Invalid user data from Supabase');
        }

        $user = User::firstOrNew(['email' => $userInfo['email']]);

        // Only set values if they're not already set (or conditionally update if needed)
        if (!$user->name || $user->name === 'Google User') {
            $user->name = $userInfo['user_metadata']['preferred_username'] ?? 'Google User';
        }

        $user->provider = 'google';
        $user->provider_id = $userInfo['user_metadata']['provider_id'] ?? null;
        $user->avatar_url = $userInfo['user_metadata']['avatar_url'] ?? null;

        $user->save();


        Auth::login($user);
        return redirect('/dashboard');
    }
}
