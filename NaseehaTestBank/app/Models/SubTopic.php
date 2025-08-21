<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTopic extends Model
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
        'main_topic_id',
    ];

    /**
     * علاقة الموضوع الفرعي مع الموضوع الرئيسي (ينتمي لموضوع رئيسي واحد)
     */
    public function mainTopic()
    {
        return $this->belongsTo(MainTopic::class);
    }

    /**
     * علاقة الموضوع الفرعي مع الأسئلة (يحتوي على أسئلة متعددة)
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}