<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/ReactionController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\Reaction;
use Truvoicer\TfPerspectives\Notifications\PerspectiveReacted;

class ReactionController extends Controller
{
    public function react(Request $request, Perspective $perspective)
    {
        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(['empathy', 'insight', 'relate', 'curious'])],
        ]);

        $userId = $request->user()->id;

        $existing = Reaction::where('perspective_id', $perspective->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing && $existing->type === $data['type']) {
            // Toggle off.
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['type' => $data['type']]);
        } else {
            Reaction::create([
                'perspective_id' => $perspective->id,
                'user_id'        => $userId,
                'type'           => $data['type'],
            ]);

            if ($perspective->user_id !== $userId) {
                $perspective->author?->notify(
                    new PerspectiveReacted($perspective, $request->user(), $data['type']),
                );
            }
        }

        return redirect()->back();
    }
}
