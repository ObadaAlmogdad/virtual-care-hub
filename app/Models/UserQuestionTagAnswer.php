<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserQuestionTagAnswer extends Model
{
    use HasFactory,SoftDeletes;
    protected $guarded=['id'];
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class);
    }

    public function consultationResult()
    {
        return $this->hasOne(ConsultationResult::class);
    }
}
