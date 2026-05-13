<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    protected $fillable = [
        'user_id',
        'id_number',
        'selfie_url',
        'id_card_url',
        'status'
    ];
}
