<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'text',
        'image',
        'answer',    // إجابة السؤال كصورة
        'unit_id',
        'lesson_id',
        'main_topic_id',
        'sub_topic_id',
        'user_id',
        'rating',    // تقييم السؤال من 1-5 نجوم
        'comment',   // تعليق/ملاحظة من المنشئ
    ];

    /**
     * علاقة السؤال مع الوحدة (ينتمي لوحدة واحدة)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * علاقة السؤال مع الدرس (ينتمي لدرس واحد)
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // علاقة التقييمات
    public function userRatings()
    {
        return $this->hasMany(UserQuestionRating::class);
    }

    // حساب متوسط التقييم
    public function getAverageRatingAttribute()
    {
        return $this->userRatings()->avg('rating') ?: 0;
    }

    // عدد المقيمين
    public function getRatingCountAttribute()
    {
        return $this->userRatings()->count();
    }

    /**
     * علاقة السؤال مع الموضوع الرئيسي (ينتمي لموضوع رئيسي واحد - اختياري)
     */
    public function mainTopic()
    {
        return $this->belongsTo(MainTopic::class);
    }

    /**
     * علاقة السؤال مع الموضوع الفرعي (ينتمي لموضوع فرعي واحد - اختياري)
     */
    public function subTopic()
    {
        return $this->belongsTo(SubTopic::class);
    }

    /**
     * علاقة السؤال مع المستخدم (ينتمي لمستخدم واحد)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الحصول على عدد النجوم كـ array للعرض في الواجهة
     */
    public function getStarsAttribute()
    {
        return [
            'filled' => $this->rating,
            'empty' => 5 - $this->rating,
            'total' => 5,
            'percentage' => ($this->rating / 5) * 100
        ];
    }

    /**
     * scope للحصول على الأسئلة حسب التقييم
     */
    public function scopeWithRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * scope للحصول على أسئلة بتقييم عالي (4-5 نجوم)
     */
    public function scopeHighRated($query)
    {
        return $query->whereIn('rating', [4, 5]);
    }

    /**
     * scope للحصول على أسئلة بتقييم منخفض (1-2 نجوم)
     */
    public function scopeLowRated($query)
    {
        return $query->whereIn('rating', [1, 2]);
    }
}