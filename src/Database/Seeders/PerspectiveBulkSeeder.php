<?php
// packages/truvoicer/tf-perspectives/database/seeders/PerspectiveBulkSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Models\Tag;
use Truvoicer\TfPerspectives\Models\User;

class PerspectiveBulkSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get()->all();
        $categories = PerspectiveCategory::query()->get()->all();

        if (empty($users) || empty($categories)) return;

        $voices = $this->voices();
        $moods = ['hopeful', 'weary', 'curious', 'grateful', 'determined', 'afraid', 'angry'];
        $tagPool = ['reflection', 'everyday', 'change', 'family', 'work', 'community', 'identity', 'loss', 'growth', 'place'];

        for ($i = 0; $i < 60; $i++) {
            $user = $users[array_rand($users)];
            $category = $categories[array_rand($categories)];
            $voice = $voices[array_rand($voices)];

            $perspective = Perspective::create([
                'user_id' => $user->id,
                'perspective_category_id' => $category->id,
                'voice' => $voice['voice'],
                'body' => $voice['body'],
                'mood' => $moods[array_rand($moods)],
                'is_featured' => $i < 4,
                'depth' => 0,
            ]);

            $tagNames = collect($tagPool)->shuffle()->take(rand(1, 3));
            $ids = $tagNames->map(fn ($name) => Tag::firstOrCreate(['name' => Str::slug($name)])->id);
            $perspective->tags()->sync($ids->all());
        }
    }

    private function voices(): array
    {
        return [
            ['voice' => 'As someone who reads the same book every year', 'body' => 'I have read Middlemarch eleven times. I find something different in it each time. I have stopped apologising for the fact that I am not reading anything new.'],
            ['voice' => 'As a person who always arrives early', 'body' => 'I arrive everywhere twenty minutes early. It is not discipline. It is the fact that I cannot stand the feeling of being watched walking into a room.'],
            ['voice' => 'As someone who never finished their degree', 'body' => 'I dropped out at twenty and never went back. Nobody asks about it now. For about ten years it was the first thing I thought about in the morning.'],
            ['voice' => 'As someone who collects objects that belonged to my family', 'body' => 'I have a drawer of things that belonged to my grandmother. None of them are valuable. All of them are the only reason I remember her handwriting.'],
            ['voice' => 'As a person who cannot drive', 'body' => 'I have never learned. I live in a city that assumes I do. Every invitation to dinner comes with a fifteen-minute argument about how I will get there.'],
            ['voice' => 'As someone who has moved back in with their parents at forty', 'body' => 'I did not think I would be here. My mother does not say anything about it. That is somehow worse than if she did.'],
            ['voice' => 'As a person who has never left their home town', 'body' => 'Everyone I grew up with has left. I stayed. I am not sure whether that is a choice or a fact about me. It might be both.'],
            ['voice' => 'As someone who keeps every card they have ever been given', 'body' => 'I have a shoebox of birthday cards going back to primary school. I am aware this is unusual. When I die I would rather someone find them than not.'],
            ['voice' => 'As a person who eats the same lunch every day', 'body' => 'I have eaten a cheese sandwich at 12:30 for fourteen years. My colleagues have stopped commenting. I find the sameness restful in a way I cannot explain.'],
            ['voice' => 'As someone who apologises when other people bump into them', 'body' => 'I have never once been the one to collide. I still say sorry. My therapist says it is about something. I think it is about a hundred somethings.'],
            ['voice' => 'As a person who has never been in a relationship', 'body' => 'I am forty-one. Everyone assumes there is a story. There is not. There is just a sequence of years in which it did not happen.'],
            ['voice' => 'As someone who has a favourite bench in the park', 'body' => 'I have sat on the same bench most mornings for six years. I have watched three of the trees get taken down. I do not know who I would tell.'],
            ['voice' => 'As a person who still writes letters', 'body' => 'I write to my brother every month. He lives twenty minutes away. Neither of us has said why we do it this way.'],
            ['voice' => 'As someone who has learned to say no', 'body' => 'It took me thirty years. The first time I said it to my mother I could not sleep for two days. It has got easier. It has not got easy.'],
            ['voice' => 'As a person who keeps a diary nobody will read', 'body' => 'I write two pages every night and I have never shown anyone. I am not sure it is for me. I am not sure who it is for.'],
        ];
    }
}
