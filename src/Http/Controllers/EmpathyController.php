<?php
// app/Http/Controllers/EmpathyController.php

namespace Truvoicer\TfPerspectives\Http\Controllers;

use Truvoicer\TfPerspectives\Models\Perspective;
use Illuminate\Http\Request;
use Truvoicer\TfPerspectives\Http\Controllers\Controller;

class EmpathyController extends Controller
{
    /** Toggle "I have stood in these shoes." */
    public function toggle(Request $request, Perspective $perspective)
    {
        $userId = $request->user()->id;

        $existing = $perspective->empathies()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
        } else {
            $perspective->empathies()->create(['user_id' => $userId]);
        }

        return redirect()->back();
    }
}
