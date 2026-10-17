<?php
// packages/truvoicer/tf-perspectives/src/Models/ShoeDiscussion.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoeDiscussion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shoe_id',
        'user_id',
        'parent_id',
        'body',
        'is_anonymous',
        'depth',
    ];

    protected $casts = [
        'shoe_id'      => 'integer',
        'user_id'      => 'integer',
        'parent_id'    => 'integer',
        'is_anonymous' => 'boolean',
        'depth'        => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function shoe(): BelongsTo
    {
        return $this->belongsTo(PerspectiveShoe::class, 'shoe_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest('id');
    }

    // ── Boot ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $discussion) {
            // Derive depth from the parent so callers never have to set it.
            if ($discussion->parent_id && $discussion->depth === null) {
                $parentDepth = (int) static::query()
                    ->whereKey($discussion->parent_id)
                    ->value('depth');

                $discussion->depth = $parentDepth + 1;
            } else {
                $discussion->depth ??= 0;
            }
        });

        static::created(function (self $discussion) {
            // Keep the shoe's counter fresh.
            $discussion->shoe()->increment('discussions_count');
        });

        static::deleted(function (self $discussion) {
            // Only decrement when the row is actually gone (soft delete still
            // fires `deleted`, so check trashed state).
            if ($discussion->isForceDeleting() || $discussion->trashed()) {
                $discussion->shoe()->decrement('discussions_count');
            }
        });
    }
}
