<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * الحصول على رابط الملف إذا كان موجود
     */
    public function getFileUrlAttribute()
    {
        if ($this->message_type !== 'text' && $this->file_path) {
            return \Storage::url($this->file_path);
        }
        return null;
    }

    /**
     * التحقق من أن الرسالة نصية
     */
    public function isText()
    {
        return $this->message_type === 'text';
    }

    /**
     * التحقق من أن الرسالة صورة
     */
    public function isImage()
    {
        return $this->message_type === 'image';
    }

    /**
     * التحقق من أن الرسالة ملف
     */
    public function isFile()
    {
        return $this->message_type === 'file';
    }
}
