<?php
// packages/truvoicer/tf-perspectives/src/Http/Controllers/ReportController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\Report;

class ReportController extends Controller
{
    public function store(Request $request, Perspective $perspective)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', Rule::in(['spam', 'harassment', 'misinformation', 'off-topic', 'other'])],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        // Prevent duplicate reports by the same user for the same perspective.
        $exists = Report::where('perspective_id', $perspective->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->exists();

        if (! $exists) {
            Report::create([
                'perspective_id' => $perspective->id,
                'user_id'        => $request->user()->id,
                'reason'         => $data['reason'],
                'notes'          => $data['notes'] ?? null,
            ]);
        }

        return redirect()->back();
    }
}
