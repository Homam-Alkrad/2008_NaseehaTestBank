<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'description'];

    // علاقة مع الوحدات
    public function units() {
        return $this->hasMany(Unit::class);
    }
}