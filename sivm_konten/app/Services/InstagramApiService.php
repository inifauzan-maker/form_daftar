<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InstagramApiService
{
    protected $client;
    protected $accessToken;
    protected $baseUrl = 'https://graph.instagram.com';

    public function __construct()
    {
        $this->client = new Client();
        $this->accessToken = config('services.instagram.access_token');
    }

    /**
     * Get Instagram account media
     */
    public function getAccountMedia($accountId, $limit = 25)
    {
        try {
            $cacheKey = "instagram_media_{$accountId}_{$limit}";
            
            return Cache::remember($cacheKey, 3600, function () use ($accountId, $limit) {
                $response = $this->client->get("{$this->baseUrl}/{$accountId}/media", [
                    'query' => [
                        'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,like_count,comments_count',
                        'limit' => $limit,
                        'access_token' => $this->accessToken
                    ]
                ]);

                return json_decode($response->getBody()->getContents(), true);
            });
        } catch (\Exception $e) {
            Log::error('Instagram API Error: ' . $e->getMessage());
            return ['data' => []];
        }
    }

    /**
     * Get media insights
     */
    public function getMediaInsights($mediaId)
    {
        try {
            $cacheKey = "instagram_insights_{$mediaId}";
            
            return Cache::remember($cacheKey, 3600, function () use ($mediaId) {
                $response = $this->client->get("{$this->baseUrl}/{$mediaId}/insights", [
                    'query' => [
                        'metric' => 'engagement,impressions,reach,saved',
                        'access_token' => $this->accessToken
                    ]
                ]);

                return json_decode($response->getBody()->getContents(), true);
            });
        } catch (\Exception $e) {
            Log::error('Instagram Insights API Error: ' . $e->getMessage());
            return ['data' => []];
        }
    }

    /**
     * Get hashtag top media
     */
    public function getHashtagTopMedia($hashtag, $limit = 25)
    {
        try {
            // First get hashtag ID
            $hashtagResponse = $this->client->get("{$this->baseUrl}/ig_hashtag_search", [
                'query' => [
                    'user_id' => config('services.instagram.user_id'),
                    'q' => $hashtag,
                    'access_token' => $this->accessToken
                ]
            ]);

            $hashtagData = json_decode($hashtagResponse->getBody()->getContents(), true);
            
            if (empty($hashtagData['data'])) {
                return ['data' => []];
            }

            $hashtagId = $hashtagData['data'][0]['id'];

            // Get top media for hashtag
            $response = $this->client->get("{$this->baseUrl}/{$hashtagId}/top_media", [
                'query' => [
                    'user_id' => config('services.instagram.user_id'),
                    'fields' => 'id,caption,media_type,media_url,permalink,like_count,comments_count',
                    'limit' => $limit,
                    'access_token' => $this->accessToken
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Instagram Hashtag API Error: ' . $e->getMessage());
            return ['data' => []];
        }
    }
}