<?php
// packages/truvoicer/tf-perspectives/src/Models/Follow.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = ['follower_id', 'followed_id'];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'follower_id');
    }

    public function followed(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'followed_id');
    }
}
