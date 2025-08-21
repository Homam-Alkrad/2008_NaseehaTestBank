<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainTopic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'lesson_id',
    ];

    /**
     * علاقة الموضوع الرئيسي مع الدرس (ينتمي لدرس واحد)
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * علاقة الموضوع الرئيسي مع المواضيع الفرعية (يحتوي على مواضيع فرعية متعددة)
     */
    public function subTopics()
    {
        return $this->hasMany(SubTopic::class);
    }

    /**
     * علاقة الموضوع الرئيسي مع الأسئلة (يحتوي على أسئلة متعددة)
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}