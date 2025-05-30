<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'consultation_id',
        'stripe_payment_intent_id',
        'amount',
        'fee',
        'net_amount',
        'status',
    ];
}
