<?php
// packages/truvoicer/tf-perspectives/database/seeders/PerspectiveViewSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveView;
use Truvoicer\TfPerspectives\Models\User;

class PerspectiveViewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id')->all();
        $perspectives = Perspective::query()->get();

        foreach ($perspectives as $perspective) {
            $isFeatured = (bool) $perspective->is_featured;

            $count = $isFeatured ? rand(150, 400) : ($perspective->parent_id === null ? rand(20, 80) : rand(5, 25));

            $viewers = collect($users)->shuffle()->take(min($count, count($users)));

            foreach ($viewers as $userId) {
                PerspectiveView::create([
                    'perspective_id' => $perspective->id,
                    'user_id' => $userId,
                    'created_at' => now()->subMinutes(rand(1, 60 * 24 * 14)),
                ]);
            }

            for ($i = count($viewers); $i < $count; $i++) {
                PerspectiveView::create([
                    'perspective_id' => $perspective->id,
                    'user_id' => null,
                    'ip_hash' => hash('sha256', 'guest-'.$perspective->id.'-'.$i),
                    'created_at' => now()->subMinutes(rand(1, 60 * 24 * 14)),
                ]);
            }
        }
    }
}
