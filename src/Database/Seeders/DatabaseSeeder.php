<?php
// packages/truvoicer/tf-perspectives/database/seeders/DatabaseSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PerspectiveCategorySeeder::class,
            UserSeeder::class,
            PerspectiveSeeder::class,
            PerspectiveBulkSeeder::class,
            ShoeSeeder::class,             // Creates the "other shoes" attached to perspectives
            ShoeDiscussionSeeder::class,   // Creates threads under specific shoes
            ReactionSeeder::class,
            BookmarkSeeder::class,
            FollowSeeder::class,
            PerspectiveViewSeeder::class,
        ]);
    }
}
