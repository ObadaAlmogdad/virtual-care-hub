<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = ['doctor_id', 'patient_id', 'rating', 'comment'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    
    // public function consultation()
    // {
    //     return $this->belongsTo(Consultation::class);
    // }
}
