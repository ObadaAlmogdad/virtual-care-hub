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
        //'duration',
        //اولوية
        //فترة انتظار
        //عدد الاستشارات الخاصة
        //اسءلة ai
        //نسة التوفير
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
} 