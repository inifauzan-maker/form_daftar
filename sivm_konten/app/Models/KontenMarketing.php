<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KontenMarketing extends Model
{
    use HasFactory;

    protected $table = 'konten_marketing';
    protected $primaryKey = 'id_konten';

    protected $fillable = [
            'judul_konten',
            'tipe_konten',
            'deskripsi',
            'platform',
            'tanggal_posting',
            'engagement_rate',
            'views',
            'likes',
            'comments',
            'share_count',
            'status',
            'creator',
            'hashtags',
            'media_files',
            'ai_generated_caption',
            'is_ai_generated',
            'type',
            'file',
    ];

    protected $casts = [
        'tanggal_posting' => 'datetime',
        'engagement_rate' => 'decimal:2',
        'media_files' => 'array',
        'is_ai_generated' => 'boolean',
    ];

    /**
     * Get the creator of the content
     */
    public function creatorUser()
    {
        return $this->belongsTo(User::class, 'creator');
    }

    /**
     * Scope for filtering by platform
     */
    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get engagement rate percentage
     */
    public function getEngagementRateAttribute($value)
    {
        return $value;
    }
}
