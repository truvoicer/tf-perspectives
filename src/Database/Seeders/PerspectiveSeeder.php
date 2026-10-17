<?php
// packages/truvoicer/tf-perspectives/database/seeders/PerspectiveSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;
use Truvoicer\TfPerspectives\Models\Tag;
use Truvoicer\TfPerspectives\Models\User;

class PerspectiveSeeder extends Seeder
{
    private array $users;
    private array $categoriesBySlug;

    public function run(): void
    {
        Perspective::query()->delete();

        $this->users = User::query()->orderBy('id')->get()->all();
        $this->categoriesBySlug = PerspectiveCategory::query()->get()->keyBy('slug')->all();

        foreach ($this->roots() as $data) {
            $this->createPerspective($data);
        }
    }

    private function createPerspective(array $data): void
    {
        $user = $this->users[array_rand($this->users)];
        $category = $this->categoriesBySlug[$data['category'] ?? ''] ?? null;

        $perspective = Perspective::create([
            'user_id'                 => $user->id,
            'perspective_category_id' => $category?->id,
            'voice'                   => $data['voice'] ?? null,
            'body'                    => $data['body'],
            'mood'                    => $data['mood'] ?? null,
            'is_anonymous'            => $data['anonymous'] ?? false,
            'is_featured'             => $data['featured'] ?? false,
            'depth'                   => 0,
        ]);

        $this->syncTags($perspective, $data['tags'] ?? []);
    }

    private function syncTags(Perspective $perspective, array $tags): void
    {
        if (empty($tags)) return;

        $ids = collect($tags)
            ->map(fn (string $name) => Tag::firstOrCreate(['name' => Str::slug($name)])->id)
            ->all();

        $perspective->tags()->sync($ids);
    }

    private function roots(): array
    {
        return [
            ['category' => 'everyday-life', 'voice' => 'As someone who works long shifts', 'mood' => 'weary', 'tags' => ['deliveries', 'neighbours'], 'body' => 'People who don\'t accept or hold my deliveries when I\'m not home are a problem. I work twelve-hour shifts and I can\'t always be at the door. I come home to a slip saying the parcel was returned and I have to take another day off to chase it.'],
            ['category' => 'neighbours-local', 'voice' => 'As someone whose child was knocked over', 'mood' => 'angry', 'tags' => ['dogs', 'parks'], 'body' => 'People should just control their dogs. My four-year-old was knocked over by a Labrador last week in the park. The owner said "he\'s friendly" and walked on. Friendly is not the point.'],
            ['category' => 'dating-romance', 'voice' => 'As someone who was ghosted after three dates', 'mood' => 'angry', 'tags' => ['dating'], 'body' => 'People who ghost after a first date are rude. Three dates and a text is not too much to ask. I\'m not owed a relationship. I\'m owed a sentence.'],
            ['category' => 'parenting', 'voice' => 'As someone on the next table', 'mood' => 'weary', 'tags' => ['parenting', 'public-space'], 'body' => 'Parents should be able to calm their children down in public. I spent a whole meal last week trying to enjoy my food while a child screamed two tables over. The parents did nothing. I\'m not saying they\'re bad parents. I\'m saying it isn\'t only their meal.'],
            ['category' => 'transport-mobility', 'voice' => 'As a city budget analyst', 'mood' => 'curious', 'tags' => ['public-transport', 'budget'], 'body' => 'Our city wants to remove the overnight bus routes to save money. From where I sit it looks like an easy line item. But I keep wondering who is on those buses at 2am, and where they would be instead.'],
            ['category' => 'remote-work', 'voice' => 'As a manager told to bring everyone back', 'mood' => 'weary', 'tags' => ['remote-work', 'return-to-office'], 'body' => 'Head office sent down a memo: three days a week in the building, effective next quarter. My team has been fully remote for four years. Half of them moved to other cities. I do not know how to have this conversation.'],
            ['category' => 'artificial-intelligence', 'voice' => 'As a translator who has watched the work dry up', 'mood' => 'angry', 'tags' => ['ai', 'work', 'language'], 'body' => 'I have translated literary fiction for fifteen years. In the last eighteen months, three of my regular clients have moved to machine translation with a "post-editor." The post-editor is paid a third of what I was paid, and the resulting text is competent but flat. Everyone seems fine with flat.'],
            ['category' => 'everyday-life', 'voice' => 'As a commuter', 'mood' => 'weary', 'tags' => ['public-space', 'manners'], 'body' => 'People who play music out loud on the train are a small daily tyranny. It is not the volume. It is that everyone else has quietly agreed to a rule that one person has decided does not apply to them.'],
            ['category' => 'technology', 'voice' => 'As a teacher who hears it every year', 'mood' => 'curious', 'tags' => ['technology', 'education'], 'body' => 'Every few years someone senior tells us that everyone should learn to code. I am not against coding. I am against the assumption that the thing standing between a child and a good life is a programming language.'],
            ['category' => 'mental-health', 'voice' => 'As someone who says it to friends', 'mood' => 'hopeful', 'tags' => ['mental-health', 'support'], 'body' => 'When a friend is going through something, I say "stay positive" because I genuinely do not know what else to say. I am aware it is not helpful. I am not sure what is.'],
            ['category' => 'transport-mobility', 'voice' => 'As a driver who nearly hit a cyclist', 'mood' => 'afraid', 'tags' => ['cycling', 'roads'], 'body' => 'I nearly hit a cyclist last week. I did everything right and they still came out of nowhere. I am not against cycling. I am afraid of driving near it, and I do not think I am alone in that.'],
            ['category' => 'everyday-life', 'voice' => 'As someone with sensory sensitivity', 'mood' => 'weary', 'tags' => ['everyday'], 'body' => 'People whose phones ring loudly on the train and then hold a whole conversation are not the worst people in the world. They are just the loudest thing that has happened to me that day, and the sound is still sitting in my chest an hour later.'],
            ['category' => 'language-culture', 'voice' => 'As someone who moved countries at fourteen', 'mood' => 'curious', 'tags' => ['language', 'belonging'], 'body' => 'I have lived here longer than I lived there. When people ask where I am from I have to choose, every time, which truth to give them. I am not sure there is a version of the answer that does not feel like a small betrayal of one place or the other.'],
        ];
    }
}
