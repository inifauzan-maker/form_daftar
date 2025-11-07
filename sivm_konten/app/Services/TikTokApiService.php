<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TikTokApiService
{
    protected $client;
    protected $accessToken;
    protected $baseUrl = 'https://open-api.tiktok.com';

    public function __construct()
    {
        $this->client = new Client();
        $this->accessToken = config('services.tiktok.access_token');
    }

    /**
     * Get TikTok user videos
     */
    public function getUserVideos($limit = 20)
    {
        try {
            $cacheKey = "tiktok_videos_{$limit}";
            
            return Cache::remember($cacheKey, 3600, function () use ($limit) {
                $response = $this->client->post("{$this->baseUrl}/v2/video/list/", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'max_count' => $limit,
                        'fields' => [
                            'id',
                            'title', 
                            'video_description',
                            'cover_image_url',
                            'create_time',
                            'share_url',
                            'like_count',
                            'comment_count',
                            'share_count',
                            'view_count'
                        ]
                    ]
                ]);

                return json_decode($response->getBody()->getContents(), true);
            });
        } catch (\Exception $e) {
            Log::error('TikTok API Error: ' . $e->getMessage());
            return ['data' => ['videos' => []]];
        }
    }

    /**
     * Get video analytics
     */
    public function getVideoAnalytics($videoId)
    {
        try {
            $cacheKey = "tiktok_analytics_{$videoId}";
            
            return Cache::remember($cacheKey, 3600, function () use ($videoId) {
                $response = $this->client->post("{$this->baseUrl}/v2/video/data/", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->accessToken,
                        'Content-Type' => 'application/json'
                    ],
                    'json' => [
                        'video_ids' => [$videoId],
                        'fields' => [
                            'like_count',
                            'comment_count',
                            'share_count',
                            'view_count',
                            'profile_view_count'
                        ]
                    ]
                ]);

                return json_decode($response->getBody()->getContents(), true);
            });
        } catch (\Exception $e) {
            Log::error('TikTok Analytics API Error: ' . $e->getMessage());
            return ['data' => []];
        }
    }

    /**
     * Search trending hashtags
     */
    public function getTrendingHashtags($country = 'ID', $limit = 10)
    {
        try {
            $cacheKey = "tiktok_trending_{$country}_{$limit}";
            
            return Cache::remember($cacheKey, 7200, function () use ($country, $limit) {
                // Note: This would require TikTok Research API which has different endpoints
                // For now, return mock data structure
                return [
                    'data' => [
                        'hashtags' => [
                            ['name' => 'properti', 'count' => 150000],
                            ['name' => 'investasi', 'count' => 120000],
                            ['name' => 'rumah', 'count' => 98000],
                            ['name' => 'villa', 'count' => 45000],
                            ['name' => 'hunian', 'count' => 32000],
                        ]
                    ]
                ];
            });
        } catch (\Exception $e) {
            Log::error('TikTok Trending API Error: ' . $e->getMessage());
            return ['data' => ['hashtags' => []]];
        }
    }
}