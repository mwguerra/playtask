<?php

namespace App\Models;

use Database\Factories\BetaSignupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BetaSignup extends Model
{
    /** @use HasFactory<BetaSignupFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'ip',
        'user_agent',
    ];
}
