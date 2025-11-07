<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KontenMarketingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            [
                'judul_konten' => 'Tips Investasi Properti untuk Pemula',
                'tipe_konten' => 'Reels',
                'deskripsi' => 'Panduan lengkap investasi properti yang menguntungkan untuk pemula dengan budget terbatas',
                'platform' => 'Instagram',
                'tanggal_posting' => '2025-10-01 10:00:00',
                'engagement_rate' => 8.5,
                'views' => 45000,
                'likes' => 3200,
                'comments' => 150,
                'share_count' => 89,
                'status' => 'Posted',
                'creator' => 2,
                'hashtags' => '#villamerah #properti #investasi #pemula #tips'
            ],
            [
                'judul_konten' => 'Review Hunian Villa Merah Terbaru',
                'tipe_konten' => 'Video',
                'deskripsi' => 'Review lengkap hunian terbaru Villa Merah dengan fasilitas premium dan lokasi strategis',
                'platform' => 'TikTok',
                'tanggal_posting' => '2025-09-28 14:30:00',
                'engagement_rate' => 12.3,
                'views' => 78000,
                'likes' => 5600,
                'comments' => 340,
                'share_count' => 245,
                'status' => 'Posted',
                'creator' => 3,
                'hashtags' => '#villamerah #review #hunian #premium #property'
            ],
            [
                'judul_konten' => 'Panduan Lengkap KPR 2025',
                'tipe_konten' => 'Artikel',
                'deskripsi' => 'Artikel komprehensif tentang syarat dan cara mengajukan KPR di tahun 2025',
                'platform' => 'Website',
                'tanggal_posting' => '2025-09-25 09:15:00',
                'engagement_rate' => 6.8,
                'views' => 12500,
                'likes' => 890,
                'comments' => 67,
                'share_count' => 34,
                'status' => 'Posted',
                'creator' => 3,
                'hashtags' => '#kpr #panduan #properti #villamerah'
            ],
            [
                'judul_konten' => 'Lokasi Strategis Villa Merah',
                'tipe_konten' => 'Carousel',
                'deskripsi' => 'Carousel showcase lokasi-lokasi strategis Villa Merah di berbagai kota',
                'platform' => 'Instagram',
                'tanggal_posting' => '2025-09-20 16:45:00',
                'engagement_rate' => 9.7,
                'views' => 32000,
                'likes' => 2400,
                'comments' => 98,
                'share_count' => 156,
                'status' => 'Posted',
                'creator' => 2,
                'hashtags' => '#villamerah #lokasi #strategis #properti'
            ],
            [
                'judul_konten' => 'Testimoni Customer Villa Merah',
                'tipe_konten' => 'Video',
                'deskripsi' => 'Video testimoni dari customer yang puas dengan pelayanan Villa Merah',
                'platform' => 'TikTok',
                'tanggal_posting' => '2025-09-18 11:20:00',
                'engagement_rate' => 15.2,
                'views' => 95000,
                'likes' => 7300,
                'comments' => 425,
                'share_count' => 380,
                'status' => 'Posted',
                'creator' => 3,
                'hashtags' => '#testimoni #customer #villamerah #puas'
            ],
            [
                'judul_konten' => 'Promo Akhir Tahun Villa Merah',
                'tipe_konten' => 'Reels',
                'deskripsi' => 'Promo spesial akhir tahun dengan diskon hingga 20% untuk semua unit',
                'platform' => 'Instagram',
                'tanggal_posting' => '2025-10-15 10:00:00',
                'engagement_rate' => 0,
                'views' => 0,
                'likes' => 0,
                'comments' => 0,
                'share_count' => 0,
                'status' => 'Scheduled',
                'creator' => 2,
                'hashtags' => '#promo #akhirtahun #diskon #villamerah'
            ],
            [
                'judul_konten' => 'Tips Memilih Hunian Impian',
                'tipe_konten' => 'Artikel',
                'deskripsi' => 'Panduan memilih hunian yang sesuai dengan kebutuhan dan budget keluarga',
                'platform' => 'Website',
                'tanggal_posting' => null,
                'engagement_rate' => 0,
                'views' => 0,
                'likes' => 0,
                'comments' => 0,
                'share_count' => 0,
                'status' => 'Draft',
                'creator' => 3,
                'hashtags' => '#tips #hunian #impian #panduan'
            ]
        ];

        foreach ($contents as $content) {
            \App\Models\KontenMarketing::create($content);
        }
    }
}
