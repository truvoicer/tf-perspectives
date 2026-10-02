<?php
// app/Http/Controllers/EmpathyController.php

namespace App\Http\Controllers;

use App\Models\Perspective;
use Illuminate\Http\Request;

class EmpathyController extends Controller
{
    /** Toggle "I have stood in these shoes." */
    public function toggle(Request $request, Perspective $perspective)
    {
        $userId = $request->user()->id;

        $existing = $perspective->empathies()->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $active = false;
        } else {
            $perspective->empathies()->create(['user_id' => $userId]);
            $active = true;
        }

        return response()->json([
            'has_empathized' => $active,
            'empathy_count'  => $perspective->empathies()->count(),
        ]);
    }
}
