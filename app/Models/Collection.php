<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    public const METADATA = [
        'age'            => 'Age',
        'gender'         => 'Gender',
        'city'           => 'City / domicile',
        'profession'     => 'Profession',
        'marital_status' => 'Marital status',
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'type',
        'price',
        'reward',
        'respondent_target',
        'reward_budget',
        'survey_ended_at',
        'status',
        'metadata_fields',
        'quality_score',
        'quality_avg',
        'cleaning_report'
    ];

    protected $casts = [
        'price'             => 'integer',
        'reward'            => 'integer',
        'respondent_target' => 'integer',
        'reward_budget'     => 'integer',
        'survey_ended_at'   => 'datetime',
        'metadata_fields'   => 'array',
        'quality_score' => 'float',
        'quality_avg' => 'float',
        'cleaning_report' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Collection $collection) {
            if (empty($collection->slug)) {
                $collection->slug = static::uniqueSlug($collection->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'dataset';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    /**
     * Escrow refund when the survey stops collecting: only the rewards for
     * uncollected slots are returned; the platform fee is never refunded.
     */
    public function unusedRewardRefund(): int
    {
        if ($this->reward_budget <= 0) {
            return 0;
        }

        $uncollected = max(0, (int) $this->respondent_target - $this->entries()->count());

        return min((int) $this->reward_budget, $uncollected * (int) $this->reward);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isFree(): bool
    {
        return (int) $this->price === 0;
    }

    public static function questionTypes(): array
    {
        return [
            'text'      => ['label' => 'Short text',     'icon' => 'minus'],
            'paragraph' => ['label' => 'Paragraph',       'icon' => 'align-left'],
            'choice'    => ['label' => 'Multiple choice', 'icon' => 'circle-dot'],
            'checkbox'  => ['label' => 'Checkbox',        'icon' => 'check-square'],
            'number'    => ['label' => 'Number',          'icon' => 'hash'],
            'rating'    => ['label' => 'Rating scale',    'icon' => 'star'],
            'datetime'  => ['label' => 'Date / Time',     'icon' => 'calendar'],
            'file'      => ['label' => 'File upload',      'icon' => 'paperclip'],
        ];
    }

    public static function blankQuestion(): array
    {
        return ['text' => '', 'type' => 'text', 'description' => '', 'required' => false, 'options' => [], 'hasOther' => false, 'maxStars' => 5, 'dateMode' => 'date'];
    }

    public static function questionsForForm(self $collection): array
    {
        return $collection->questions->map(fn ($q) => [
            'text'        => $q->title,
            'type'        => $q->type,
            'description' => $q->description ?? '',
            'required'    => (bool) ($q->required ?? false),
            'options'     => $q->options ?? [],
            'hasOther'    => (bool) ($q->has_other ?? false),
            'maxStars'    => $q->max_stars ?? 5,
            'dateMode'    => $q->date_mode ?? 'date',
        ])->values()->all();
    }

    public function cleanState(): string
    {
        if ($this->entries()->whereNotNull('clean2_data')->exists()) {
            return 'clean2';
        }

        if ($this->entries()->whereNotNull('clean1_data')->exists()) {
            return 'clean1';
        }

        return 'raw';
    }

    public function averageRating(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
    }

    public function purchasedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->purchases()->where('user_id', $user->id)->exists();
    }
}
