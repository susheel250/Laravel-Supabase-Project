<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class SupabaseHelper
{
    public static function getUserInfo($accessToken)
    {
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get('https://hxqpwmdypzscudyoqehk.supabase.co/auth/v1/user', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'apikey' => config('services.supabase.key'), // uses config/services.php
                ]
            ]);

            $userInfo = json_decode((string) $response->getBody(), true);

            \Log::info('Supabase user info:', $userInfo);

            return $userInfo;
        } catch (\Exception $e) {
            \Log::error('Supabase fetch failed: ' . $e->getMessage());
            return null;
        }
    }
}
