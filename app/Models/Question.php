<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    public const TYPES = ['text', 'paragraph', 'choice', 'checkbox', 'number', 'rating', 'datetime', 'file'];

    protected $fillable = ['collection_id', 'title', 'type', 'description', 'options', 'position', 'required', 'max_stars', 'date_mode', 'has_other'];

    protected $casts = [
        'options'   => 'array',
        'required'  => 'boolean',
        'has_other' => 'boolean',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
