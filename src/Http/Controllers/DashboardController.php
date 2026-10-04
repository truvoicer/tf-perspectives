<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/DashboardController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Truvoicer\TfPerspectives\Http\Controllers\Concerns\TransformsPerspectives;
use Truvoicer\TfPerspectives\Models\Perspective;

class DashboardController extends Controller
{
    use TransformsPerspectives;

    public function index(Request $request): Response
    {
        $me = $request->user();

        $perspectiveIds = Perspective::where('user_id', $me->id)->pluck('id');

        $reactionsReceived = DB::table('reactions')
            ->whereIn('perspective_id', $perspectiveIds)
            ->count();

        $bookmarks = DB::table('bookmarks')
            ->where('user_id', $me->id)
            ->count();

        $viewsWeek = 0; // populate if you track views
        if (class_exists(\Truvoicer\TfPerspectives\Models\PerspectiveView::class)) {
            $viewsWeek = \Truvoicer\TfPerspectives\Models\PerspectiveView::query()
                ->whereIn('perspective_id', $perspectiveIds)
                ->where('created_at', '>=', now()->subWeek())
                ->count();
        }

        $topPerspective = Perspective::where('user_id', $me->id)
            ->withCount('reactions')
            ->orderByDesc('reactions_count')
            ->first();

        $topHydrated = null;
        if ($topPerspective) {
            $q = Perspective::query()->where('id', $topPerspective->id);
            $this->withCardRelations($q, $me->id);
            $top = $q->first();
            $topHydrated = $top ? $this->transform($top, $me->id) : null;
        }

        $recentActivity = $me->notifications()
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($n) => [
                'id'             => $n->id,
                'type'           => $n->data['kind'] ?? 'notification',
                'message'        => $n->data['message'] ?? 'New activity',
                'created_at'     => optional($n->created_at)->toIso8601String(),
                'perspective_id' => $n->data['perspective_id'] ?? 0,
            ])
            ->all();

        return Inertia::render('@tf-perspectives::dashboard', [
            'stats' => [
                'perspectives'        => Perspective::where('user_id', $me->id)->whereNull('parent_id')->count(),
                'branches'            => Perspective::where('user_id', $me->id)->whereNotNull('parent_id')->count(),
                'reactions_received'  => $reactionsReceived,
                'bookmarks'           => $bookmarks,
                'views_week'          => $viewsWeek,
                'top_perspective'     => $topHydrated,
            ],
            'recent_activity' => $recentActivity,
        ]);
    }
}
