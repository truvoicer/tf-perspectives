<?php
// packages/truvoicer/tf-perspectives/database/seeders/FollowSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Truvoicer\TfPerspectives\Models\Follow;
use Truvoicer\TfPerspectives\Models\User;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id')->all();
        $total = count($users);

        foreach ($users as $i => $followerId) {
            $weight = ($i % 5 === 0) ? rand(10, min(25, $total - 1)) : rand(0, 6);
            if ($weight === 0) continue;

            $targets = collect($users)
                ->reject(fn ($id) => $id === $followerId)
                ->shuffle()
                ->take($weight);

            foreach ($targets as $followedId) {
                Follow::firstOrCreate([
                    'follower_id' => $followerId,
                    'followed_id' => $followedId,
                ]);
            }
        }
    }
}
