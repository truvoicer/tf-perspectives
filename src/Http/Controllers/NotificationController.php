<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/NotificationController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Services\CategoryService;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $paginator = $user->notifications()
            ->latest()
            ->paginate(20)
            ->through(function ($n) {
                return [
                    'id'             => $n->id,
                    'type'           => $n->data['kind'] ?? 'notification',
                    'actor'          => $n->data['actor'] ?? null,
                    'message'        => $n->data['message'] ?? 'New activity',
                    'perspective_id' => $n->data['perspective_id'] ?? 0,
                    'created_at'     => $n->created_at->toIso8601String(),
                    'read_at'        => $n->read_at?->toIso8601String(),
                ];
            });

        return \Inertia\Inertia::render('@tf-perspectives::notifications', [
            'notifications' => $paginator,
        ]);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }

    public function read(Request $request, string $id)
    {
        $request->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        return redirect()->back();
    }
}
