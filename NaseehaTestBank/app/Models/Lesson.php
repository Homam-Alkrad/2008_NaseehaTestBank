<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
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
        'unit_id',
    ];

    /**
     * علاقة الدرس مع الوحدة (ينتمي لوحدة واحدة)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * علاقة الدرس مع المواضيع الرئيسية (يحتوي على مواضيع متعددة)
     */
    public function mainTopics()
    {
        return $this->hasMany(MainTopic::class);
    }

    /**
     * علاقة الدرس مع الأسئلة (يحتوي على أسئلة متعددة)
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}