<?php
// app/Models/Perspective.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perspective extends Model
{
    use HasFactory;

    /** How deep a chain of "shoes" can go before it bottoms out. */
    public const MAX_DEPTH = 5;

    protected $fillable = ['user_id', 'parent_id', 'root_id', 'voice', 'body', 'depth'];

    protected $casts = [
        'depth'     => 'integer',
        'parent_id' => 'integer',
        'root_id'   => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest('id');
    }

    public function empathies(): HasMany
    {
        return $this->hasMany(PerspectiveEmpathy::class);
    }

    /** The id of the top of this thread (itself, if it has no parent). */
    public function rootId(): int
    {
        return (int) ($this->root_id ?? $this->id);
    }

    /** Every node in a thread: the root plus all of its descendants. */
    public function scopeThread(Builder $query, int $rootId): Builder
    {
        return $query->where(function (Builder $q) use ($rootId) {
            $q->where('id', $rootId)->orWhere('root_id', $rootId);
        });
    }
}
