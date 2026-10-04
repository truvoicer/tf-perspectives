<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/ProfileController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\Perspective;

class ProfileController extends Controller
{
    use TransformsPerspectives;

    public function show(Request $request, string $handle): Response
    {
        $viewerId = auth()->id();

        $profile = \App\Models\User::query()
            ->where('handle', $handle)
            ->firstOrFail();

        $tab = (string) $request->input('tab', 'perspectives');

        $query = Perspective::query()
            ->where('user_id', $profile->id);

        match ($tab) {
            'branches'  => $query->whereNotNull('parent_id')->latest('id'),
            'bookmarks' => $viewerId === $profile->id
                ? Perspective::query()
                    ->whereIn('id', function ($sub) use ($profile) {
                        $sub->select('perspective_id')
                            ->from('bookmarks')
                            ->where('user_id', $profile->id);
                    })
                    ->latest('id')
                : $query->whereRaw('1 = 0'), // hidden from others
            default     => $query->whereNull('parent_id')->latest('id'),
        };

        $this->withCardRelations($query, $viewerId);

        $paginator = $query->paginate(12)->withQueryString()
            ->through(fn (Perspective $p) => $this->transform($p, $viewerId));

        $isFollowing = $viewerId && $viewerId !== $profile->id
            ? $profile->followers()->where('follower_id', $viewerId)->exists()
            : false;

        return Inertia::render('@tf-perspectives::profile/show', [
            'profile' => [
                'id'               => $profile->id,
                'name'             => $profile->name,
                'handle'           => $profile->handle ?? 'user-'.$profile->id,
                'avatar_url'       => $profile->avatar_url ?? null,
                'bio'              => $profile->bio ?? null,
                'is_following'     => $isFollowing,
                'followers_count'  => $profile->followers()->count(),
                'following_count'  => $profile->following()->count(),
                'perspectives_count' => $profile->perspectives()->count(),
            ],
            'perspectives' => $paginator,
            'tab'          => in_array($tab, ['perspectives', 'branches', 'bookmarks'], true)
                ? $tab
                : 'perspectives',
            'stats' => [
                'perspectives'     => $profile->perspectives()->whereNull('parent_id')->count(),
                'branches'         => $profile->perspectives()->whereNotNull('parent_id')->count(),
                'empathy_received' => $profile->reactionsReceived()->where('type', 'empathy')->count(),
                'followers'        => $profile->followers()->count(),
                'following'        => $profile->following()->count(),
            ],
        ]);
    }
}
