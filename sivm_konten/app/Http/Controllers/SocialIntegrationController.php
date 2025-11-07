<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SocialIntegrationController extends Controller
{
    public function index()
    {
        return view('social_integration');
    }

    /**
     * Handle OAuth callback dari Instagram/TikTok
     */
    public function callback(Request $request, $platform)
    {
        $code = $request->get('code');
        
        if (!$code) {
            return redirect()->route('social.integration')->with('error', 'Authorization failed');
        }

        if ($platform === 'instagram') {
            return $this->handleInstagramCallback($code);
        } elseif ($platform === 'tiktok') {
            return $this->handleTikTokCallback($code);
        }

        return redirect()->route('social.integration');
    }

    /**
     * Handle Instagram OAuth callback
     */
    private function handleInstagramCallback($code)
    {
        try {
            // Exchange code for access token
            $response = Http::post('https://api.instagram.com/oauth/access_token', [
                'client_id' => config('services.instagram.client_id'),
                'client_secret' => config('services.instagram.client_secret'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => route('social.callback', 'instagram'),
                'code' => $code,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store access token in session/database
                Session::put('instagram_access_token', $data['access_token']);
                Session::put('instagram_user_id', $data['user_id']);
                Session::put('instagram_connected', true);

                // Get user info
                $userResponse = Http::get('https://graph.instagram.com/me', [
                    'fields' => 'id,username,media_count',
                    'access_token' => $data['access_token']
                ]);

                if ($userResponse->successful()) {
                    $userData = $userResponse->json();
                    Session::put('instagram_username', $userData['username']);
                    Session::put('instagram_followers', $userData['media_count'] ?? 0);
                }

                return redirect()->route('social.integration')->with('success', 'Instagram berhasil terhubung!');
            }
        } catch (\Exception $e) {
            return redirect()->route('social.integration')->with('error', 'Gagal menghubungkan Instagram: ' . $e->getMessage());
        }

        return redirect()->route('social.integration')->with('error', 'Gagal menghubungkan Instagram');
    }

    /**
     * Handle TikTok OAuth callback
     */
    private function handleTikTokCallback($code)
    {
        try {
            // Exchange code for access token
            $response = Http::post('https://open-api.tiktok.com/oauth/access_token/', [
                'client_key' => config('services.tiktok.client_key'),
                'client_secret' => config('services.tiktok.client_secret'),
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => route('social.callback', 'tiktok'),
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'];
                
                // Store access token in session/database
                Session::put('tiktok_access_token', $data['access_token']);
                Session::put('tiktok_open_id', $data['open_id']);
                Session::put('tiktok_connected', true);

                // Get user info
                $userResponse = Http::post('https://open-api.tiktok.com/user/info/', [
                    'access_token' => $data['access_token'],
                    'open_id' => $data['open_id'],
                    'fields' => 'open_id,union_id,avatar_url,display_name,follower_count'
                ]);

                if ($userResponse->successful()) {
                    $userData = $userResponse->json()['data']['user'];
                    Session::put('tiktok_username', $userData['display_name']);
                    Session::put('tiktok_followers', $userData['follower_count'] ?? 0);
                }

                return redirect()->route('social.integration')->with('success', 'TikTok berhasil terhubung!');
            }
        } catch (\Exception $e) {
            return redirect()->route('social.integration')->with('error', 'Gagal menghubungkan TikTok: ' . $e->getMessage());
        }

        return redirect()->route('social.integration')->with('error', 'Gagal menghubungkan TikTok');
    }

    /**
     * Disconnect social media account
     */
    public function disconnect(Request $request, $platform)
    {
        if ($platform === 'instagram') {
            Session::forget([
                'instagram_access_token',
                'instagram_user_id', 
                'instagram_connected',
                'instagram_username',
                'instagram_followers'
            ]);
        } elseif ($platform === 'tiktok') {
            Session::forget([
                'tiktok_access_token',
                'tiktok_open_id',
                'tiktok_connected',
                'tiktok_username',
                'tiktok_followers'
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Save API settings
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'instagram_token' => 'nullable|string',
            'tiktok_key' => 'nullable|string',
        ]);

        // Save to config or database
        if ($request->instagram_token) {
            Session::put('instagram_manual_token', $request->instagram_token);
        }

        if ($request->tiktok_key) {
            Session::put('tiktok_manual_key', $request->tiktok_key);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Post content to Instagram
     */
    public function postToInstagram(Request $request)
    {
        $accessToken = Session::get('instagram_access_token');
        
        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Instagram tidak terhubung']);
        }

        try {
            // Create media object
            $mediaResponse = Http::post('https://graph.instagram.com/me/media', [
                'image_url' => $request->image_url,
                'caption' => $request->caption,
                'access_token' => $accessToken
            ]);

            if ($mediaResponse->successful()) {
                $mediaId = $mediaResponse->json()['id'];
                
                // Publish media
                $publishResponse = Http::post("https://graph.instagram.com/me/media_publish", [
                    'creation_id' => $mediaId,
                    'access_token' => $accessToken
                ]);

                if ($publishResponse->successful()) {
                    return response()->json(['success' => true, 'message' => 'Berhasil posting ke Instagram']);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal posting ke Instagram: ' . $e->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal posting ke Instagram']);
    }

    /**
     * Post content to TikTok
     */
    public function postToTikTok(Request $request)
    {
        $accessToken = Session::get('tiktok_access_token');
        
        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'TikTok tidak terhubung']);
        }

        try {
            // Upload video to TikTok
            $response = Http::post('https://open-api.tiktok.com/share/video/upload/', [
                'access_token' => $accessToken,
                'open_id' => Session::get('tiktok_open_id'),
                'video' => $request->video_url,
                'text' => $request->caption,
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Berhasil posting ke TikTok']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal posting ke TikTok: ' . $e->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal posting ke TikTok']);
    }
}