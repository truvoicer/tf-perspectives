<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Perspective;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $people = collect([
            'Ada'  => 'ada@example.com',
            'Malik'=> 'malik@example.com',
            'Jun'  => 'jun@example.com',
            'Priya'=> 'priya@example.com',
        ])->map(fn ($email, $name) => User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make('password'),
        ]))->values();

        $root = Perspective::create([
            'user_id' => $people[0]->id,
            'body'    => 'Our city wants to remove the overnight bus routes to save money. '
                       . 'From where I sit it looks like an easy line item. But I keep wondering '
                       . 'who is on those buses at 2am, and where they would be instead.',
            'voice'   => 'As a city budget analyst',
        ]);

        $child = Perspective::create([
            'user_id'   => $people[1]->id,
            'parent_id' => $root->id,
            'root_id'   => $root->id,
            'depth'     => 1,
            'voice'     => 'As a hospital cleaner on the night shift',
            'body'      => 'That 2am bus is the only way I get home. A taxi costs two hours of my pay. '
                         . 'If it goes, I do not lose a convenience. I lose the job.',
        ]);

        Perspective::create([
            'user_id'   => $people[2]->id,
            'parent_id' => $child->id,
            'root_id'   => $root->id,
            'depth'     => 2,
            'voice'     => 'As the bus driver',
            'body'      => 'I have driven that route for nine years. Twelve people a night, same twelve. '
                         . 'I know their stops. I also know the depot cannot keep losing money forever.',
        ]);

        Perspective::create([
            'user_id'   => $people[3]->id,
            'parent_id' => $root->id,
            'root_id'   => $root->id,
            'depth'     => 1,
            'voice'     => 'As the person who wrote the proposal',
            'body'      => 'I did not want to cut it. I had a number I had to hit and no one gave me '
                         . 'a different number to hit. Tell me what to trade instead and I will listen.',
        ]);
    }
}
