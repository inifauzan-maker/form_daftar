<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InstagramApiService;
use App\Services\TikTokApiService;
use App\Services\AiContentService;
use Illuminate\Http\Request;

class ContentApiController extends Controller
{
    protected $instagramService;
    protected $tiktokService;
    protected $aiService;

    public function __construct(
        InstagramApiService $instagramService,
        TikTokApiService $tiktokService,
        AiContentService $aiService
    ) {
        $this->instagramService = $instagramService;
        $this->tiktokService = $tiktokService;
        $this->aiService = $aiService;
    }

    /**
     * Get Instagram content research data
     */
    public function getInstagramResearch(Request $request)
    {
        $hashtag = $request->get('hashtag', 'properti');
        $limit = $request->get('limit', 10);

        try {
            $topMedia = $this->instagramService->getHashtagTopMedia($hashtag, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $topMedia,
                'hashtag' => $hashtag
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch Instagram data',
                'message' => config('app.debug') ? $e->getMessage() : 'Service temporarily unavailable'
            ], 500);
        }
    }

    /**
     * Get TikTok trending hashtags
     */
    public function getTikTokTrending(Request $request)
    {
        $country = $request->get('country', 'ID');
        $limit = $request->get('limit', 10);

        try {
            $trending = $this->tiktokService->getTrendingHashtags($country, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $trending
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch TikTok data',
                'message' => config('app.debug') ? $e->getMessage() : 'Service temporarily unavailable'
            ], 500);
        }
    }

    /**
     * Generate AI content
     */
    public function generateContent(Request $request)
    {
        $request->validate([
            'type' => 'required|in:caption,hashtags',
            'topic' => 'required|string|max:200',
            'platform' => 'required|in:instagram,tiktok,website',
            'tone' => 'nullable|string|in:friendly,professional,casual,formal'
        ]);

        $topic = $request->get('topic');
        $platform = $request->get('platform');
        $type = $request->get('type');
        $tone = $request->get('tone', 'friendly');

        try {
            if ($type === 'caption') {
                $result = $this->aiService->generateCaption($topic, $platform, $tone);
            } else {
                $count = $request->get('count', 10);
                $result = $this->aiService->generateHashtags($topic, $platform, $count);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'AI service error',
                'message' => config('app.debug') ? $e->getMessage() : 'AI service temporarily unavailable'
            ], 500);
        }
    }

    /**
     * Check for duplicate content
     */
    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'platform' => 'nullable|string|in:instagram,tiktok,website'
        ]);

        $content = $request->get('content');
        $platform = $request->get('platform');

        try {
            $result = $this->aiService->checkDuplicate($content, $platform);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Duplicate check failed',
                'message' => config('app.debug') ? $e->getMessage() : 'Service temporarily unavailable'
            ], 500);
        }
    }

    /**
     * Get content suggestions based on trending topics
     */
    public function getContentSuggestions(Request $request)
    {
        $platform = $request->get('platform', 'instagram');
        $category = $request->get('category', 'properti');

        // Mock suggestions - in production this would analyze trending data
        $suggestions = [
            'properti' => [
                [
                    'title' => 'Tips Memilih Lokasi Properti Strategis',
                    'description' => 'Panduan lengkap memilih lokasi properti yang menguntungkan',
                    'hashtags' => '#properti #investasi #lokasistrategis #villamerah',
                    'engagement_potential' => 'high'
                ],
                [
                    'title' => 'Tren Harga Properti 2025',
                    'description' => 'Analisis tren dan prediksi harga properti tahun ini',
                    'hashtags' => '#trendproperti #hargaproperti #analisis #investasi',
                    'engagement_potential' => 'medium'
                ],
                [
                    'title' => 'Review Fasilitas Villa Merah Terbaru',
                    'description' => 'Tour virtual fasilitas premium di project terbaru',
                    'hashtags' => '#villamerah #review #fasilitas #premium',
                    'engagement_potential' => 'high'
                ]
            ],
            'investasi' => [
                [
                    'title' => 'Cara Memulai Investasi Properti dengan Modal Minim',
                    'description' => 'Tips praktis investasi properti untuk pemula',
                    'hashtags' => '#investasi #properti #modalminim #pemula',
                    'engagement_potential' => 'high'
                ]
            ]
        ];

        $categorySuggestions = $suggestions[$category] ?? $suggestions['properti'];

        return response()->json([
            'success' => true,
            'data' => [
                'platform' => $platform,
                'category' => $category,
                'suggestions' => $categorySuggestions
            ]
        ]);
    }
}
