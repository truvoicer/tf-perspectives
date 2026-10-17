<?php
// packages/truvoicer/tf-perspectives/database/seeders/ShoeDiscussionSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Truvoicer\TfPerspectives\Models\ShoeDiscussion;
use Truvoicer\TfPerspectives\Models\PerspectiveShoe;
use Truvoicer\TfPerspectives\Models\User;

class ShoeDiscussionSeeder extends Seeder
{
    private array $users;

    public function run(): void
    {
        ShoeDiscussion::query()->forceDelete();

        $this->users = User::query()->get()->all();

        foreach ($this->discussions() as $targetVoice => $threads) {
            // Match the shoe by its exact voice
            $shoe = PerspectiveShoe::where('voice', $targetVoice)->first();

            if (! $shoe) {
                continue;
            }

            foreach ($threads as $thread) {
                $user = $this->users[array_rand($this->users)];

                // Top-level reply
                $parent = ShoeDiscussion::create([
                    'shoe_id' => $shoe->id,
                    'user_id' => $user->id,
                    'body'    => $thread['body'],
                    'depth'   => 0,
                ]);
                $shoe->increment('discussions_count');

                // Optional nested reply
                if (isset($thread['reply'])) {
                    $replyUser = $this->users[array_rand($this->users)];
                    ShoeDiscussion::create([
                        'shoe_id'   => $shoe->id,
                        'user_id'   => $replyUser->id,
                        'parent_id' => $parent->id,
                        'body'      => $thread['reply'],
                        'depth'     => 1,
                    ]);
                    $shoe->increment('discussions_count');
                }
            }
        }
    }

    private function discussions(): array
    {
        return [
            'As a neighbour with a disability' => [
                [
                    'body' => 'I had no idea. I thought the neighbours I ask were just '
                            . 'being kind. I never stopped to think about what it costs them.',
                    'reply' => 'It costs more than people think, but I would still rather '
                             . 'be asked than ignored.',
                ],
            ],
            'As someone whose parcel was stolen after a neighbour signed for it' => [
                [
                    'body' => 'I sign for fifty parcels a day and I hate it. It\'s not '
                            . 'my job, it just became the only way to hit the numbers. '
                            . 'Somebody above me decided a signature was proof and '
                            . 'never asked whether I had the authority to give one.',
                ],
            ],
            'As a rescue-dog owner' => [
                [
                    'body' => '"He\'s friendly" is almost always said by someone who has '
                            . 'never had to see what happens when a dog is not. The '
                            . 'sentence isn\'t about the dog. It\'s about the owner\'s '
                            . 'comfort, said out loud.',
                ],
            ],
            'As someone who has been stalked by a date' => [
                [
                    'body' => 'Reading this I realise I have never once thought about '
                            . 'safety when deciding how to end a date. I\'ve always '
                            . 'assumed the sentence was the respectful thing. It '
                            . 'didn\'t occur to me that for some people the sentence '
                            . 'is the opening.',
                ],
            ],
            'As a parent of an autistic child' => [
                [
                    'body' => 'The kindest thing I ever did was keep a small stack of '
                            . 'sensory toys behind the till. It cost twelve pounds '
                            . 'and saved about one walkout a month. Nobody asks '
                            . 'restaurants for this because nobody thinks to.',
                ],
            ],
            'As a hospital cleaner on the night shift' => [
                [
                    'body' => 'I have driven that route for nine years. Twelve people '
                            . 'a night, same twelve. I know their stops. I also know '
                            . 'the depot cannot keep losing money forever.',
                ],
            ],
            'As a new parent on the team' => [
                [
                    'body' => 'I keep hearing "think of the parents" and I do, but I '
                            . 'also get more done at home and I do not want to give '
                            . 'that up for a policy that mostly exists so someone '
                            . 'can walk past my desk. I am not against flexibility. '
                            . 'I am against the assumption that my life is the '
                            . 'flexible one.',
                ],
            ],
            'As a project manager who made the switch' => [
                [
                    'body' => 'I have read both versions of one of the books you '
                            . 'translated. The machine version is fine. It is also '
                            . 'the version I forgot within a week. I do not know '
                            . 'how to argue for the difference in a spreadsheet.',
                ],
            ],
            'As a teenager who does it' => [
                [
                    'body' => 'I did it for two years and I never once thought about '
                            . 'how it landed. I heard about it when I was twenty. '
                            . 'The thing I remember is not the shame. It is that '
                            . 'somebody bothered to tell me, gently, in a way I '
                            . 'could hear.',
                ],
            ],
            'As a carer with no spare hours' => [
                [
                    'body' => 'I did it. It took three years and I had a partner who '
                            . 'could carry the rent. I got lucky in ways I did not '
                            . 'control. When I hear "anyone can do it" I nod, and '
                            . 'privately I know who paid for my ability to.',
                ],
            ],
            'As someone who has been on the receiving end' => [
                [
                    'body' => '"I don\'t know what to say" said honestly is worth '
                            . 'more than "stay positive" said comfortably. The first '
                            . 'one admits you are with the person. The second one '
                            . 'tells them they are alone with it.',
                ],
            ],
            'As a daily cyclist' => [
                [
                    'body' => 'The road we are arguing about was designed in the '
                            . 'sixties for cars and only cars. When you put a '
                            . 'cyclist on it, both parties are doing something the '
                            . 'space was not designed to allow. The failure is not '
                            . 'the driver or the cyclist. It is the design.',
                ],
            ],
            'As someone taking an urgent call' => [
                [
                    'body' => 'There is a "quiet coach" on every train. Almost nobody '
                            . 'sits in it. The people who need it are the ones who '
                            . 'don\'t know it exists. I put a sign up once. It was '
                            . 'unread for two years.',
                ],
            ],
            'As the parent who moved the family' => [
                [
                    'body' => 'I do not remember the other place at all. My brother '
                            . 'does. Sometimes I feel like I got the easier version '
                            . 'of the story and he got the truer one.',
                ],
            ],
        ];
    }
}
