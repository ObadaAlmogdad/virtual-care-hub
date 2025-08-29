<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration',
        'is_active',
        'priority',
        'expected_wait_minutes',
        'private_consultations_quota',
        'ai_consultations_quota',
        'max_family_members',
        'savings_percent',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
} 