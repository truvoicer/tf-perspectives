<?php
// packages/truvoicer/tf-perspectives/src/Models/Bookmark.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    use HasFactory;

    protected $fillable = ['perspective_id', 'user_id'];

    public function perspective(): BelongsTo
    {
        return $this->belongsTo(Perspective::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
