<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'picture_url', 'phone_number', 'gender', 'dob',
        'address', 'city', 'profession', 'marital_status',
    ];

    protected $casts = ['dob' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function age(): ?int
    {
        return $this->dob ? (int) Carbon::parse($this->dob)->diffInYears(now()) : null;
    }

    /**
     * The profile column backing each metadata key a survey can request. Age is
     * the odd one out: it is derived, so what we actually store is the birth date.
     *
     * @var array<string, string>
     */
    public const METADATA_SOURCES = [
        'age'            => 'dob',
        'gender'         => 'gender',
        'city'           => 'city',
        'profession'     => 'profession',
        'marital_status' => 'marital_status',
    ];

    public function metadata(): array
    {
        return [
            'age'            => $this->age(),
            'gender'         => $this->gender,
            'city'           => $this->city,
            'profession'     => $this->profession,
            'marital_status' => $this->marital_status,
        ];
    }

    /**
     * Of the metadata a survey asked for, the keys this profile cannot answer yet.
     * The respondent is asked for these on the survey form so the entry is not
     * saved with the very fields the survey was created to collect left blank.
     *
     * @param  array<int, string>  $requested
     * @return array<int, string>
     */
    public function missingMetadata(array $requested): array
    {
        return array_values(array_filter(
            $requested,
            fn ($key) => isset(self::METADATA_SOURCES[$key]) && blank($this->{self::METADATA_SOURCES[$key]}),
        ));
    }
}
