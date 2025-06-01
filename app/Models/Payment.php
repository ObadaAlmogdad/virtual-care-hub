<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'doctor_id',
        'consultation_id',
        'stripe_payment_intent_id',
        'amount',
        'fee',
        'net_amount',
        'status',
        'is_refunded',
        'refunded_at'
    ];

    protected $casts = [
        'is_refunded' => 'boolean',
        'refunded_at' => 'datetime',
        'amount' => 'integer',
        'fee' => 'integer',
        'net_amount' => 'integer'
    ];

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    // التحقق من الحالة
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSucceeded()
    {
        return $this->status === 'succeeded';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function canBeRefunded()
    {
        return $this->isSucceeded() && !$this->is_refunded;
    }
}
