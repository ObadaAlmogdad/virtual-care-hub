<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complaint extends Model
{
    use HasFactory;
   protected $fillable = [
    'consultation_id',
    'user_id',
    'header',
    'content',
    'type',
    'media',
    'answer'
];

protected $casts = [
    'media' => 'array',
];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
