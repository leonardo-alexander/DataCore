<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'slug',
        'source',
        'price',
        'status',
        'quality_score',
        'cleaning_report',
        'file_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function accesses()
    {
        return $this->hasMany(CollectionAccess::class);
    }

    public function hasAccess($userId)
    {
        if (!$userId) return false;

        if ($this->user_id === $userId) {
            return true;
        }

        return $this->accesses()
            ->where('user_id', $userId)
            ->exists();
    }
}
