<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'payment_method',
        'payment_id',
        'remaining_private_consultations',
        'remaining_ai_consultations',
        'family_code',
        'max_family_members',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'subscription_members')
            ->withTimestamps();
    }
} 