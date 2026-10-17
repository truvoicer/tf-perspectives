<?php
// packages/truvoicer/tf-perspectives/src/Models/Report.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    public const REASONS = ['spam', 'harassment', 'misinformation', 'off-topic', 'other'];

    protected $fillable = ['perspective_id', 'user_id', 'reason', 'notes', 'status'];

    public function perspective(): BelongsTo
    {
        return $this->belongsTo(Perspective::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
