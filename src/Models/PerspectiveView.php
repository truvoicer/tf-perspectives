<?php
// packages/truvoicer/tf-perspectives/src/Models/PerspectiveView.php

namespace Truvoicer\TfPerspectives\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerspectiveView extends Model
{
    use HasFactory;

    protected $fillable = [
        'perspective_id',
        'user_id',
        'ip_hash',
        'user_agent',
    ];

    public function perspective(): BelongsTo
    {
        return $this->belongsTo(Perspective::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Record a view once per viewer per perspective per day.
     *
     * `$viewer` may be an authenticated user or null (guest).
     * Guests are de-duplicated by a hash of IP + user agent.
     */
    public static function record(Perspective $perspective, $viewer = null): void
    {
        $userId = $viewer?->id;

        $ipHash = $userId
            ? null
            : hash('sha256', request()->ip().'|'.(string) request()->userAgent());

        $today = now()->startOfDay();

        $already = static::query()
            ->where('perspective_id', $perspective->id)
            ->where('created_at', '>=', $today)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->whereNull('user_id')->where('ip_hash', $ipHash))
            ->exists();

        if ($already) {
            return;
        }

        static::create([
            'perspective_id' => $perspective->id,
            'user_id'        => $userId,
            'ip_hash'        => $ipHash,
            'user_agent'     => mb_substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
