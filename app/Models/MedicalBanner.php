<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalBanner extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'image_url', 'link', 'is_active', 'expires_at'];
}
