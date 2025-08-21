<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
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
        'subject_id'  // 👈 أضف هذا السطر
    ];

    // 👈 أضف هذه العلاقة بعد العلاقات الموجودة
    public function subject() {
        return $this->belongsTo(Subject::class);
    }

    /**
     * علاقة الوحدة مع الدروس (تحتوي على دروس متعددة)
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * علاقة الوحدة مع الأسئلة (تحتوي على أسئلة متعددة)
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}