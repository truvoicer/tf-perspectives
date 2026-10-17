<?php
// packages/truvoicer/tf-perspectives/database/seeders/ReactionSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\Reaction;
use Truvoicer\TfPerspectives\Models\User;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id')->all();
        $perspectives = Perspective::query()->get();

        foreach ($perspectives as $perspective) {
            $count = $this->weightedCount();
            $pool = collect($users)->reject(fn ($id) => $id === $perspective->user_id);
            $reactors = $pool->shuffle()->take($count);

            foreach ($reactors as $userId) {
                Reaction::firstOrCreate(
                    ['perspective_id' => $perspective->id, 'user_id' => $userId],
                    ['type' => $this->pickType()],
                );
            }
        }
    }

    private function weightedCount(): int
    {
        $roll = rand(1, 100);
        return match (true) {
            $roll <= 35 => 0,
            $roll <= 60 => rand(1, 2),
            $roll <= 85 => rand(3, 6),
            default     => rand(7, 12),
        };
    }

    private function pickType(): string
    {
        $roll = rand(1, 100);
        return match (true) {
            $roll <= 45 => 'empathy',
            $roll <= 70 => 'relate',
            $roll <= 90 => 'insight',
            default     => 'curious',
        };
    }
}
