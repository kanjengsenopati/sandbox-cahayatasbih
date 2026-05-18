<?php

namespace App\Models;


use App\Traits\UuidTrait;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UuidTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'access_scope',
        'avatar',
        'is_active',
        'gender',
        'fcm_token',
        'password',
        'last_login',
        'status',
        'kta',
        'member_branch',
        'member_group',
        'rejection_note',
    ];

    /**
     * Relation to Officer (Petugas) if this user is a petugas.
     */
    public function officer()
    {
        return $this->hasOne(Officer::class);
    }


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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function student()
    {
        return $this->hasMany(Student::class);
    }

    public function scopeHasSchool($query)
    {
        // Assuming 'adminSchool' is a relationship returning the school IDs the admin can access
        $admin = Auth::user();
        $schoolIds = $admin?->adminSchool?->pluck('school_id');
        $query->whereHas('student', function ($query) use ($schoolIds) {
            $query->whereHas('classroom', function ($query) use ($schoolIds) {
                $query->whereHas('school', function ($query) use ($schoolIds) {
                    $query->whereIn('id', $schoolIds);
                });
            });
        });
    }
}
