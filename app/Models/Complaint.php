<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
   protected $fillable = [
    'consultation_id',
    'user_id',
    'header',
    'content',
    'type',
    'media'
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
