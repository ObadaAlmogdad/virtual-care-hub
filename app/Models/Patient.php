<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'height',
        'weight',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
