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
        $user = User::updateOrCreate(
            ['email' => $userInfo['email']],
            [
                'name' => $userInfo['user_metadata']['preferred_username'] ?? 'GitHub User',
                'provider' => $userInfo['app_metadata']['provider'] ?? 'github',
                'provider_id' => $userInfo['user_metadata']['provider_id'] ?? null,
                'avatar_url' => $userInfo['user_metadata']['avatar_url'] ?? null,
            ]
        );
        
        

        // Laravel login
        Auth::login($user);

        return redirect('/dashboard');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('status', 'Logged out successfully');
    }
}
