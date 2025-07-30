<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bio',
        'activatePoint',
        'rating',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'address',
        'work_days',
        'work_time_in',
        'work_time_out',
        'time_for_waiting',
        'certificate_images',
    ];

    protected $casts = [
        'work_days' => 'array',
        'certificate_images' => 'array',
        'work_time_in' => 'datetime:H:i:s',
        'work_time_out' => 'datetime:H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function specialties()
{
    return $this->hasMany(DoctorSpecialty::class);
}


    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }



}
