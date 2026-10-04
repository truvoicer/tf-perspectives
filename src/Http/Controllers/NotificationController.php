<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/NotificationController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $paginator = $user->notifications()
            ->latest()
            ->paginate(20)
            ->through(function ($n) {
                $data = $n->data ?? [];

                return [
                    'id'                   => $n->id,
                    'type'                 => $n->type,
                    'read_at'              => optional($n->read_at)->toIso8601String(),
                    'created_at'           => optional($n->created_at)->toIso8601String(),
                    'actor'                => $data['actor'] ?? null,
                    'perspective_id'       => $data['perspective_id'] ?? 0,
                    'perspective_excerpt'  => $data['perspective_excerpt'] ?? '',
                    'message'              => $data['message'] ?? 'New activity',
                ];
            });

        return Inertia::render('@tf-perspectives::notifications', [
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
