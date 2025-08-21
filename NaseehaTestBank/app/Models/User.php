<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * علاقة المستخدم مع الأسئلة (يملك أسئلة متعددة)
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * تحقق من كون المستخدم مدير نظام
     */
    public function isSysAdmin()
    {
        return $this->role === 'SysAdmin';
    }

    /**
     * تحقق من كون المستخدم عادي
     */
    public function isUser()
    {
        return $this->role === 'User';
    }
}