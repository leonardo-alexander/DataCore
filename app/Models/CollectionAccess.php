<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionAccess extends Model
{
    protected $fillable = [
        'user_id',
        'collection_id',
        // 'transaction_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    // public function transaction(): BelongsTo
    // {
    //     return $this->belongsTo(Transaction::class);
    // }

    public static function grant($userId, $collectionId, $source = 'purchase')
    {
        return self::firstOrCreate([
            'user_id' => $userId,
            'collection_id' => $collectionId,
        ], [
            'source' => $source,
        ]);
    }
}
