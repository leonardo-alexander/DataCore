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
}
