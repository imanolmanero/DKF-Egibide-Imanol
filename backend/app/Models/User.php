<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Alumnos;

class User extends Authenticatable {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the alumno associated with this user
     */
    public function alumno(): HasOne {
        return $this->hasOne(Alumnos::class,'user_id','id');
    }

    /**
     * Get the tutor associated with this user
     */
    public function tutorEgibide(): HasOne {
        return $this->hasOne(TutorEgibide::class,'user_id', 'id');
    }

    /**
     * Get the instructor associated with this user
     */
    public function instructor(): HasOne {
        return $this->hasOne(TutorEmpresa::class,'user_id', 'id');
    }
}
