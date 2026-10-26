<?php

namespace Truvoicer\TfPerspectives\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Truvoicer\TfPerspectives\Services\CategoryService;
use Truvoicer\TruFetcherCore\Http\Middleware\HandleInertiaRequests as MiddlewareHandleInertiaRequests;

class HandleInertiaRequests extends MiddlewareHandleInertiaRequests
{
    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var \Truvoicer\TfPerspectives\Models\User|null $user */
        $user = $this->getUser();

         return array_merge(parent::share($request), [
            'categories'   => Inertia::optional(fn () => CategoryService::getAllCategories()),
            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'first_name' => $user->first_name,
                    'last_name'  => $user->last_name,
                    'full_name'  => $user->full_name,
                    'display_name' => $user->display_name,
                ] : null,
            ],
            'unread_notifications' => fn () => $user?->unreadNotifications()->count() ?? 0,
            'latest_notifications' => fn () => $user?->notifications()
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn ($n) => [
                    'id'             => $n->id,
                    'type'           => $n->data['kind'] ?? 'notification',
                    'actor'          => $n->data['actor'] ?? null,
                    'message'        => $n->data['message'] ?? 'New activity',
                    'perspective_id' => $n->data['perspective_id'] ?? 0,
                    'created_at'     => $n->created_at->toIso8601String(),
                    'read_at'        => $n->read_at?->toIso8601String(),
                ])
                ->all(),
        ]);
    }
}
