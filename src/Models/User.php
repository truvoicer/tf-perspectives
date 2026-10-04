<?php
// app/Models/User.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden   = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function perspectives(): HasMany
    {
        return $this->hasMany(Perspective::class);
    }

    public function empathies(): HasMany
    {
        return $this->hasMany(PerspectiveEmpathy::class);
    }


public function following()
{
    return $this->belongsToMany(
        self::class,
        'follows',
        'follower_id',
        'followed_id',
    )->withTimestamps();
}

public function followers()
{
    return $this->belongsToMany(
        self::class,
        'follows',
        'followed_id',
        'follower_id',
    )->withTimestamps();
}

public function reactionsReceived()
{
    return $this->hasManyThrough(
        \Truvoicer\TfPerspectives\Models\Reaction::class,
        \Truvoicer\TfPerspectives\Models\Perspective::class,
        'user_id',           // perspectives.user_id
        'perspective_id',    // reactions.perspective_id
        'id',                // users.id
        'id',                // perspectives.id
    );
}

}
