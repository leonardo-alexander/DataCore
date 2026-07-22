<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    protected $fillable = ['collection_id', 'user_id', 'raw_data', 'clean1_data', 'clean2_data', 'status', 'metadata'];

    protected $casts = [
        'raw_data'   => 'array',
        'clean1_data' => 'array',
        'clean2_data' => 'array',
        'metadata'   => 'array',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
