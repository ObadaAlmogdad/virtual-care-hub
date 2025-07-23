<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'fakeName',
        'height',
        'weight',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicalHistory()
    {
        return $this->hasOne(MedicalHistory::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }


}
