<?php
// packages/truvoicer/tf-perspectives/src/Models/PerspectiveShoe.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerspectiveShoe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'perspective_id',
        'user_id',
        'voice',
        'body',
        'mood',
        'is_anonymous',
        'impact_rank',
        'changed_my_view',
        'marked_by_author_at',
    ];

    protected $casts = [
        'perspective_id'       => 'integer',
        'user_id'              => 'integer',
        'is_anonymous'         => 'boolean',
        'impact_rank'          => 'integer',
        'changed_my_view'      => 'boolean',
        'marked_by_author_at'  => 'datetime',
        'discussions_count'    => 'integer',
        'empathies_count'      => 'integer',
    ];

    public const MAX_DISCUSSION_DEPTH = 4;

    // ── Relations ────────────────────────────────────────────────────────

    public function perspective(): BelongsTo
    {
        return $this->belongsTo(Perspective::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(ShoeDiscussion::class, 'shoe_id');
    }

    /**
     * Only the top-level discussions. Threaded replies are reachable via
     * each discussion's own `children()` relation.
     */
    public function rootDiscussions(): HasMany
    {
        return $this->discussions()->whereNull('parent_id')->oldest('id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    /**
     * Default ordering for a perspective page: highest impact first, then
     * most-reacted, then newest.
     */
    public function scopeRanked(Builder $query): Builder
    {
        return $query
            ->orderByRaw('impact_rank IS NULL')   // ranked shoes float to top
            ->orderBy('impact_rank')
            ->orderByDesc('empathies_count')
            ->orderByDesc('created_at');
    }

    /** Only shoes the perspective author has marked as view-changing. */
    public function scopeChangedView(Builder $query): Builder
    {
        return $query->where('changed_my_view', true);
    }
}
