<?php
// packages/truvoicer/tf-perspectives/src/Models/Reaction.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    use HasFactory;

    public const TYPES = ['empathy', 'insight', 'relate', 'curious'];

    protected $fillable = ['perspective_id', 'user_id', 'type'];

    public function perspective(): BelongsTo
    {
        return $this->belongsTo(Perspective::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
