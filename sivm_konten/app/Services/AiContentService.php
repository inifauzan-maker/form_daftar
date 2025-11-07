<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;

class AiContentService
{
    /**
     * Generate caption using AI
     */
    public function generateCaption($topic, $platform = 'instagram', $tone = 'friendly')
    {
        $cacheKey = "ai_caption_" . md5($topic . $platform . $tone);
        
        return Cache::remember($cacheKey, 3600, function () use ($topic, $platform, $tone) {
            try {
                $prompt = $this->buildCaptionPrompt($topic, $platform, $tone);
                
                $response = OpenAI::completions()->create([
                    'model' => 'gpt-3.5-turbo-instruct',
                    'prompt' => $prompt,
                    'max_tokens' => 300,
                    'temperature' => 0.7,
                ]);

                return [
                    'success' => true,
                    'caption' => trim($response['choices'][0]['text'])
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => 'AI service unavailable',
                    'fallback_caption' => $this->getFallbackCaption($topic, $platform)
                ];
            }
        });
    }

    /**
     * Generate hashtags using AI
     */
    public function generateHashtags($topic, $platform = 'instagram', $count = 10)
    {
        $cacheKey = "ai_hashtags_" . md5($topic . $platform . $count);
        
        return Cache::remember($cacheKey, 3600, function () use ($topic, $platform, $count) {
            try {
                $prompt = $this->buildHashtagPrompt($topic, $platform, $count);
                
                $response = OpenAI::completions()->create([
                    'model' => 'gpt-3.5-turbo-instruct',
                    'prompt' => $prompt,
                    'max_tokens' => 150,
                    'temperature' => 0.5,
                ]);

                $hashtags = $this->parseHashtags($response['choices'][0]['text']);
                
                return [
                    'success' => true,
                    'hashtags' => $hashtags
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => 'AI service unavailable',
                    'fallback_hashtags' => $this->getFallbackHashtags($topic, $platform)
                ];
            }
        });
    }

    /**
     * Check for duplicate content
     */
    public function checkDuplicate($content, $platform = null)
    {
        // Simple duplicate check implementation
        // In production, this would use more sophisticated similarity algorithms
        
        $query = \App\Models\KontenMarketing::query();
        
        if ($platform) {
            $query->where('platform', $platform);
        }
        
        $existingContent = $query->get(['deskripsi', 'judul_konten']);
        
        $similarity = 0;
        $matches = [];
        
        foreach ($existingContent as $existing) {
            $titleSimilarity = $this->calculateSimilarity($content, $existing->judul_konten);
            $descSimilarity = $this->calculateSimilarity($content, $existing->deskripsi);
            
            $maxSimilarity = max($titleSimilarity, $descSimilarity);
            
            if ($maxSimilarity > $similarity) {
                $similarity = $maxSimilarity;
            }
            
            if ($maxSimilarity > 0.7) {
                $matches[] = [
                    'title' => $existing->judul_konten,
                    'similarity' => $maxSimilarity
                ];
            }
        }
        
        return [
            'is_duplicate' => $similarity > 0.8,
            'similarity_score' => $similarity,
            'potential_matches' => $matches,
            'recommendation' => $similarity > 0.8 ? 'Content too similar to existing posts' : 'Content is unique enough'
        ];
    }

    /**
     * Build caption generation prompt
     */
    private function buildCaptionPrompt($topic, $platform, $tone)
    {
        return "Generate a {$tone} {$platform} caption about {$topic} for Villa Merah property company. " .
               "The caption should be engaging, use Indonesian language, and include a call-to-action. " .
               "Keep it under 200 characters for Instagram or 150 characters for TikTok.";
    }

    /**
     * Build hashtag generation prompt
     */
    private function buildHashtagPrompt($topic, $platform, $count)
    {
        return "Generate {$count} relevant hashtags for a {$platform} post about {$topic} " .
               "for Villa Merah property company. Mix popular and niche hashtags. " .
               "Include Indonesian and English hashtags. Format: #hashtag1 #hashtag2 etc.";
    }

    /**
     * Parse hashtags from AI response
     */
    private function parseHashtags($text)
    {
        preg_match_all('/#\w+/', $text, $matches);
        return array_slice($matches[0], 0, 15); // Limit to 15 hashtags
    }

    /**
     * Calculate text similarity
     */
    private function calculateSimilarity($text1, $text2)
    {
        $text1 = strtolower(trim($text1));
        $text2 = strtolower(trim($text2));
        
        if (empty($text1) || empty($text2)) {
            return 0;
        }
        
        // Simple similarity calculation
        similar_text($text1, $text2, $percent);
        return $percent / 100;
    }

    /**
     * Get fallback captions when AI is unavailable
     */
    private function getFallbackCaption($topic, $platform)
    {
        $templates = [
            'instagram' => [
                'properti' => '🏡 Temukan hunian impian Anda bersama Villa Merah! Investasi terbaik untuk masa depan cerah. #VillaMerah #Properti #Investasi',
                'investasi' => '💰 Mulai investasi properti yang menguntungkan! Villa Merah hadir dengan lokasi strategis dan fasilitas premium. #Investasi #VillaMerah',
                'default' => '✨ Villa Merah - Wujudkan impian hunian Anda! Hubungi kami untuk info lebih lanjut. #VillaMerah #Properti'
            ],
            'tiktok' => [
                'properti' => 'Villa Merah punya tips investasi properti nih! Swipe untuk lihat faktanya 🏠💰 #VillaMerah #Properti',
                'investasi' => 'Cara investasi properti yang bener gimana sih? Yuk simak tips dari Villa Merah! 💡 #Investasi #Tips',
                'default' => 'Villa Merah - hunian impian dengan lokasi strategis! 🏡 #VillaMerah #Hunian'
            ]
        ];
        
        $platformTemplates = $templates[$platform] ?? $templates['instagram'];
        $topicKey = strtolower($topic);
        
        return $platformTemplates[$topicKey] ?? $platformTemplates['default'];
    }

    /**
     * Get fallback hashtags when AI is unavailable
     */
    private function getFallbackHashtags($topic, $platform)
    {
        $commonHashtags = ['#villamerah', '#properti', '#hunian', '#investasi'];
        
        $topicHashtags = [
            'properti' => ['#property', '#rumah', '#apartment', '#villa'],
            'investasi' => ['#investment', '#bisnis', '#keuangan', '#passive income'],
            'tips' => ['#tips', '#tutorial', '#panduan', '#edukasi'],
            'review' => ['#review', '#testimoni', '#experience', '#feedback']
        ];
        
        $topic = strtolower($topic);
        $specific = $topicHashtags[$topic] ?? ['#lifestyle', '#quality', '#premium', '#strategic'];
        
        return array_merge($commonHashtags, $specific);
    }
}