<?php
// packages/truvoicer/tf-perspectives/database/seeders/ShoeSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Truvoicer\TfPerspectives\Models\Perspective;
use Truvoicer\TfPerspectives\Models\PerspectiveShoe;
use Truvoicer\TfPerspectives\Models\User;

class ShoeSeeder extends Seeder
{
    private array $users;

    public function run(): void
    {
        // Clear shoes to make re-running safe.
        PerspectiveShoe::query()->forceDelete();

        $this->users = User::query()->get()->all();

        foreach ($this->mappings() as $rootBodyPhrase => $shoes) {
            // Find the perspective this shoe belongs to.
            $root = Perspective::where('body', 'like', '%' . $rootBodyPhrase . '%')->first();

            if (! $root) {
                continue;
            }

            foreach ($shoes as $shoeData) {
                $user = $this->users[array_rand($this->users)];

                PerspectiveShoe::create([
                    'perspective_id'  => $root->id,
                    'user_id'         => $user->id,
                    'voice'           => $shoeData['voice'],
                    'body'            => $shoeData['body'],
                    'mood'            => $shoeData['mood'] ?? null,
                    'is_anonymous'    => false,
                    'changed_my_view' => $shoeData['changed_view'] ?? false,
                    'impact_rank'     => $shoeData['rank'] ?? null,
                ]);
            }
        }
    }

    private function mappings(): array
    {
        return [
            'People who don\'t accept or hold my deliveries' => [
                [
                    'voice' => 'As a neighbour with a disability',
                    'mood'  => 'weary',
                    'body'  => 'I\'m the neighbour who people leave parcels with. I\'m also the '
                             . 'neighbour who takes ten minutes to get to the door on a bad day. '
                             . 'When someone knocks five times and I can\'t answer, I feel the '
                             . 'weight of every eye roll on the street.',
                    'changed_view' => true,
                    'rank' => 1,
                ],
                [
                    'voice' => 'As someone whose parcel was stolen after a neighbour signed for it',
                    'mood'  => 'afraid',
                    'body'  => 'I used to say yes to everyone. Then a neighbour signed for a '
                             . 'package that turned out to be my laptop, and it disappeared. I '
                             . 'haven\'t accepted a delivery for anyone since. I know it looks '
                             . 'like I don\'t care. It isn\'t that.',
                    'rank' => 2,
                ],
                [
                    'voice' => 'As a woman living alone',
                    'mood'  => 'afraid',
                    'body'  => 'I don\'t open my door to strangers. That\'s the whole reason I '
                             . 'started refusing deliveries. It\'s not about you. I don\'t know '
                             . 'who\'s knocking and I can\'t tell from the peephole.',
                ],
                [
                    'voice' => 'As a former courier',
                    'mood'  => 'hopeful',
                    'body'  => 'Every rejected delivery counts against the driver. The recipient '
                             . 'never sees that. I used to spend hours at the end of a shift '
                             . 're-attempting drops that people refused for reasons I never '
                             . 'learned. The system punishes the wrong person at every step.',
                ],
            ],

            'People should just control their dogs' => [
                [
                    'voice' => 'As a rescue-dog owner',
                    'mood'  => 'weary',
                    'body'  => 'I adopted a dog that was abused for the first two years of his '
                             . 'life. He is gentle with me and terrified of everyone else. I keep '
                             . 'him on a lead everywhere. I also see him flinch when a child '
                             . 'runs at him and I know whose fault that would look like if it '
                             . 'went wrong.',
                ],
                [
                    'voice' => 'As a person who uses a wheelchair',
                    'mood'  => 'afraid',
                    'body'  => 'I have been knocked out of my chair by an off-lead dog. The '
                             . 'owner was apologetic and I believe him. It doesn\'t change the '
                             . 'fact that I now avoid the park entirely. I have given up a '
                             . 'green space because someone else\'s comfort outweighs mine.',
                    'changed_view' => true,
                ],
                [
                    'voice' => 'As someone who grew up afraid of dogs',
                    'mood'  => 'afraid',
                    'body'  => 'I was bitten at eight. I am fifty-three. I still feel it in my '
                             . 'chest when a dog runs at me. I know the dog is friendly. My body '
                             . 'does not.',
                ],
            ],

            'People who ghost after a first date' => [
                [
                    'voice' => 'As someone who has been stalked by a date',
                    'mood'  => 'afraid',
                    'body'  => 'I ghosted a man last year. He sent me forty messages over four '
                             . 'days. I reported him and nothing happened. Sending a "polite '
                             . 'decline" felt like inviting him to argue with me. Ghosting '
                             . 'was the only exit I had.',
                    'changed_view' => true,
                    'rank' => 1,
                ],
                [
                    'voice' => 'As someone with severe social anxiety',
                    'mood'  => 'afraid',
                    'body'  => 'I have ghosted people I liked. I have ghosted people I wanted '
                             . 'to see again. The moment it comes time to write the follow-up '
                             . 'message my whole body stops cooperating. I don\'t need to be '
                             . 'forgiven for it. I just don\'t want it to be read as a verdict '
                             . 'on the other person.',
                ],
                [
                    'voice' => 'As someone who was ghosted and later found out why',
                    'mood'  => 'hopeful',
                    'body'  => 'I ran into her at a party eighteen months later. She told me '
                             . 'her mother had been diagnosed with cancer that week. I had '
                             . 'spent a year building a story about what I did wrong. It '
                             . 'wasn\'t about me at all. It usually isn\'t.',
                ],
            ],

            'Parents should be able to calm their children' => [
                [
                    'voice' => 'As a parent of an autistic child',
                    'mood'  => 'weary',
                    'body'  => 'My son is seven and a restaurant is a sensorially overwhelming '
                             . 'place. When he screams, it is not a tantrum. It is a nervous '
                             . 'system in overload. I have tried everything. I have also '
                             . 'stopped going out, which is another way of disappearing from '
                             . 'the world you still get to be in.',
                    'changed_view' => true,
                ],
                [
                    'voice' => 'As a survivor of childhood punishment',
                    'mood'  => 'afraid',
                    'body'  => 'Every time a stranger watches a parent handle a child, I hear '
                             . 'my father\'s voice. He cared very much what other people '
                             . 'thought. What he did to me at home was in service of that. '
                             . 'Being watched in public is not neutral for everyone.',
                ],
                [
                    'voice' => 'As someone who works with young children',
                    'body'  => 'The children who cry loudest in public are usually the ones '
                             . 'who feel safest with their parents. The quiet ones are the '
                             . 'ones I worry about. Nothing in the scene at the next table '
                             . 'tells you what you think it tells you.',
                ],
            ],

            'Our city wants to remove the overnight bus routes' => [
                [
                    'voice' => 'As a hospital cleaner on the night shift',
                    'mood'  => 'weary',
                    'body'  => 'That 2am bus is the only way I get home. A taxi costs two hours '
                             . 'of my pay. If it goes, I do not lose a convenience. I lose the '
                             . 'job.',
                    'changed_view' => true,
                    'rank' => 1,
                ],
                [
                    'voice' => 'As the person who wrote the proposal',
                    'mood'  => 'weary',
                    'body'  => 'I did not want to cut it. I had a number I had to hit and no '
                             . 'one gave me a different number to hit. Tell me what to trade '
                             . 'instead and I will listen.',
                ],
                [
                    'voice' => 'As a woman who took that bus home from a late shift',
                    'mood'  => 'afraid',
                    'body'  => 'When they cut the bus, I stopped taking the late shifts. Then I '
                             . 'stopped being offered them. The route wasn\'t a public '
                             . 'service. It was the thing that let me keep saying yes.',
                ],
            ],

            'Head office sent down a memo' => [
                [
                    'voice' => 'As a new parent on the team',
                    'mood'  => 'afraid',
                    'body'  => 'I moved two hours away because I could not afford anything '
                             . 'closer. My childcare is set up around drop-off and pickup here. '
                             . 'If I have to be in the office three days a week I will spend '
                             . 'four hours a day commuting and I will see my daughter awake '
                             . 'for maybe forty minutes.',
                    'changed_view' => true,
                ],
                [
                    'voice' => 'As someone on the spectrum',
                    'mood'  => 'determined',
                    'body'  => 'The office is exhausting in ways I cannot easily explain. At '
                             . 'home I can control the light, the noise, the interruptions. My '
                             . 'output doubled when we went remote. Being asked to come back '
                             . 'feels like being asked to be worse at my job so that other '
                             . 'people can feel comfortable.',
                ],
                [
                    'voice' => 'As someone who lives alone and prefers the office',
                    'mood'  => 'hopeful',
                    'body'  => 'I know I am in the minority here. The office is where I meet '
                             . 'people. Fully remote was the loneliest year of my adult life. '
                             . 'I don\'t want to force anyone back. I do want someone to '
                             . 'notice that not everyone is losing something.',
                ],
            ],

            'I have translated literary fiction for fifteen years' => [
                [
                    'voice' => 'As a project manager who made the switch',
                    'mood'  => 'weary',
                    'body'  => 'I did not want to. The budget was cut by forty percent and I '
                             . 'had to choose between closing the department or finding a '
                             . 'cheaper workflow. I chose. I know exactly what we lost.',
                ],
                [
                    'voice' => 'As a first-generation immigrant',
                    'mood'  => 'hopeful',
                    'body'  => 'I read in two languages and the machine translation is what I '
                             . 'can afford. It is not what I would choose. It is the difference '
                             . 'between reading and not reading. Sometimes flat is enough.',
                    'changed_view' => true,
                ],
                [
                    'voice' => 'As an AI researcher',
                    'mood'  => 'curious',
                    'body'  => 'I build these systems and I still cannot tell you what they '
                             . 'will do to work in ten years. Anyone who tells you they can '
                             . 'is selling something. What I can tell you is that we are '
                             . 'deploying faster than we are understanding.',
                ],
            ],

            'People who play music out loud on the train' => [
                [
                    'voice' => 'As a teenager who does it',
                    'mood'  => 'curious',
                    'body'  => 'I know it annoys people. I do it anyway. My headphones broke '
                             . 'and I haven\'t been able to buy new ones. I\'m not saying '
                             . 'that makes it right. I\'m saying when I see a grown-up '
                             . 'tutting at me, I know they think I\'m doing it on purpose.',
                ],
                [
                    'voice' => 'As someone who is hard of hearing and uses a hearing aid',
                    'mood'  => 'weary',
                    'body'  => 'My hearing aid amplifies everything. A phone speaker two '
                             . 'seats away is not background noise to me. It is a wall. I '
                             . 'have stopped taking the train at rush hour and started '
                             . 'paying for taxis on the days I can afford it.',
                    'changed_view' => true,
                ],
                [
                    'voice' => 'As a tube worker',
                    'body'  => 'I have asked a hundred people to turn it down. Everyone says '
                             . '"sorry" and turns it off. Nobody has ever argued with me. '
                             . 'The commuters who suffer most are the ones who never ask.',
                ],
            ],

            'Every few years someone senior tells us that everyone should learn to code' => [
                [
                    'voice' => 'As a carer with no spare hours',
                    'mood'  => 'weary',
                    'body'  => 'I look after my mother. I would love to learn to code. The '
                             . 'distance between me and a free online course is not talent. '
                             . 'It is the two hours a day I spend helping her dress and eat.',
                ],
                [
                    'voice' => 'As someone who works with their hands',
                    'mood'  => 'hopeful',
                    'body'  => 'I am a carpenter. I am not uninterested in code. I am tired of '
                             . 'being told my work is the fallback. There is nothing small '
                             . 'about the thing I do. It just doesn\'t fit in a slide deck.',
                ],
                [
                    'voice' => 'As a person with dyslexia',
                    'mood'  => 'weary',
                    'body'  => 'I have tried three times to learn to code and each time I have '
                             . 'hit the same wall. It is not that I do not understand the '
                             . 'logic. It is that the syntax breaks my reading before I can '
                             . 'reach the logic. There are other paths in. Nobody ever points '
                             . 'at them.',
                    'changed_view' => true,
                ],
            ],

            'When a friend is going through something' => [
                [
                    'voice' => 'As someone who has been on the receiving end',
                    'mood'  => 'weary',
                    'body'  => 'The sentence does not land the way you mean it. It sounds '
                             . 'like "your sadness is a choice you are making." I know you '
                             . 'do not mean it that way. The gap between what you mean and '
                             . 'what I hear is not something I can bridge when I am at my '
                             . 'lowest.',
                    'changed_view' => true,
                ],
                [
                    'voice' => 'As someone who was raised not to complain',
                    'mood'  => 'weary',
                    'body'  => 'I was taught to smile through everything. For twenty years I '
                             . 'was the most positive person in every room and I nearly did '
                             . 'not survive it. The version of me that asks for help is the '
                             . 'version I had to build from scratch in my thirties.',
                ],
                [
                    'voice' => 'As a friend who learned what to say',
                    'mood'  => 'hopeful',
                    'body'  => 'I stopped trying to fix it. I started saying "that sounds '
                             . 'really hard" and then being quiet. It turns out the quiet '
                             . 'was the thing. Nobody had ever told me.',
                ],
            ],

            'I nearly hit a cyclist last week' => [
                [
                    'voice' => 'As a daily cyclist',
                    'mood'  => 'weary',
                    'body'  => 'I did not come out of nowhere. I was there the whole time. '
                             . 'Every near miss I have had, the driver has said the same '
                             . 'thing. I believe them. I also think the infrastructure is '
                             . 'what keeps the sentence true.',
                ],
                [
                    'voice' => 'As someone who cannot afford a car',
                    'mood'  => 'weary',
                    'body'  => 'I cycle because the alternative is an hour and a half on '
                             . 'three buses. When people say cyclists should pay road tax, '
                             . 'I hear "the poor should stay off the road." That might not '
                             . 'be what they mean. It is what lands.',
                ],
                [
                    'voice' => 'As a bus driver',
                    'body'  => 'I see everything on the road. The worst thing I ever saw was '
                             . 'a cyclist and a lorry at a junction. Neither was doing '
                             . 'anything illegal. Both were doing what the space let them. '
                             . 'The car-versus-bike argument is the wrong one.',
                    'changed_view' => true,
                ],
            ],

            'People whose phones ring loudly on the train' => [
                [
                    'voice' => 'As someone taking an urgent call',
                    'mood'  => 'afraid',
                    'body'  => 'My father was in hospital. I got a call from a nurse who had '
                             . 'twenty minutes before her shift ended. I took it in the '
                             . 'carriage because there was nowhere to go. I could feel the '
                             . 'whole carriage watching and I did not care. If that is the '
                             . 'person who ruined your commute, I am sorry. I would do it '
                             . 'again.',
                ],
                [
                    'voice' => 'As a mother whose children make noise everywhere',
                    'mood'  => 'weary',
                    'body'  => 'The sound of my phone on a train is not my favourite thing '
                             . 'either. But my son calls me every day at 4 and if I don\'t '
                             . 'answer he worries. I am not playing music. I am being a '
                             . 'mother. There is no carriage for that.',
                ],
            ],

            'I have lived here longer than I lived there' => [
                [
                    'voice' => 'As the parent who moved the family',
                    'mood'  => 'weary',
                    'body'  => 'I made a decision for my children that I will never be able '
                             . 'to explain fully. I gave them safety and took away their '
                             . 'language. I tell myself it was worth it. I am not always '
                             . 'sure.',
                ],
                [
                    'voice' => 'As someone whose grandparents migrated',
                    'mood'  => 'hopeful',
                    'body'  => 'My grandmother arrived with one suitcase and a phone number. '
                             . 'I have her suitcase. I keep it because the version of me '
                             . 'that does not know about it is a version I do not want to be.',
                ],
                [
                    'voice' => 'As someone who learned the language as an adult',
                    'body'  => 'I can speak fluently now. I still dream in my first language. '
                             . 'I do not know which one is more honest.',
                ],
            ],
        ];
    }
}
