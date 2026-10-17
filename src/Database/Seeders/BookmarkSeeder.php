<?php
// packages/truvoicer/tf-perspectives/database/seeders/BookmarkSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Truvoicer\TfPerspectives\Models\Bookmark;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\User;

class BookmarkSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();
        $perspectives = Perspective::query()->pluck('id')->all();

        foreach ($users as $user) {
            $count = rand(0, 3);
            if (rand(1, 100) <= 10) {
                $count = rand(6, 15);
            }

            if ($count === 0) continue;

            foreach (collect($perspectives)->shuffle()->take($count) as $perspectiveId) {
                Bookmark::firstOrCreate([
                    'user_id' => $user->id,
                    'perspective_id' => $perspectiveId,
                ]);
            }
        }
    }
}
