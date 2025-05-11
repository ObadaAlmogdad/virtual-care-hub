<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded=['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function consultationResults()
    {
        return $this->hasOne(ConsultationResult::class);
    }
}
