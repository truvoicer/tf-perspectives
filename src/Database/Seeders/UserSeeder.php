<?php
// packages/truvoicer/tf-perspectives/database/seeders/UserSeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Truvoicer\TfPerspectives\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->people() as $p) {
            User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'first_name'   => $p['first'],
                    'last_name'    => $p['last'],
                    'display_name' => $p['display'],
                    'password'     => 'password',
                ],
            );
        }
    }

    private function people(): array
    {
        return [
            ['first' => 'Ada',     'last' => 'Lovelace',    'email' => 'ada@example.com',     'display' => 'ada'],
            ['first' => 'Malik',   'last' => 'Osei',        'email' => 'malik@example.com',   'display' => 'malik.o'],
            ['first' => 'Jun',     'last' => 'Tanaka',      'email' => 'jun@example.com',     'display' => 'jun'],
            ['first' => 'Priya',   'last' => 'Raman',       'email' => 'priya@example.com',   'display' => 'priya.r'],
            ['first' => 'Marcus',  'last' => 'Delgado',     'email' => 'marcus@example.com',  'display' => 'marcus.d'],
            ['first' => 'Ines',    'last' => 'Fernandes',   'email' => 'ines@example.com',    'display' => 'ines'],
            ['first' => 'Tobias',  'last' => 'Bauer',       'email' => 'tobias@example.com',  'display' => 'tobias.b'],
            ['first' => 'Amara',   'last' => 'Nwosu',       'email' => 'amara@example.com',   'display' => 'amara'],
            ['first' => 'Hana',    'last' => 'Yamamoto',    'email' => 'hana@example.com',    'display' => 'hana.y'],
            ['first' => 'Ravi',    'last' => 'Kapoor',      'email' => 'ravi@example.com',    'display' => 'ravi'],
            ['first' => 'Sofia',   'last' => 'Rossi',       'email' => 'sofia@example.com',   'display' => 'sofia.rossi'],
            ['first' => 'Daniel',  'last' => 'Okafor',      'email' => 'daniel@example.com',  'display' => 'daniel.o'],
            ['first' => 'Yuki',    'last' => 'Sato',        'email' => 'yuki@example.com',    'display' => 'yuki'],
            ['first' => 'Leila',   'last' => 'Haddad',      'email' => 'leila@example.com',   'display' => 'leila.h'],
            ['first' => 'Jonas',   'last' => 'Lindqvist',   'email' => 'jonas@example.com',   'display' => 'jonas.l'],
            ['first' => 'Camila',  'last' => 'Vega',        'email' => 'camila@example.com',  'display' => 'camila'],
            ['first' => 'Omar',    'last' => 'Rashid',      'email' => 'omar@example.com',    'display' => 'omar.r'],
            ['first' => 'Naomi',   'last' => 'Adeyemi',     'email' => 'naomi@example.com',   'display' => 'naomi'],
            ['first' => 'Sven',    'last' => 'Eriksen',     'email' => 'sven@example.com',    'display' => 'sven.e'],
            ['first' => 'Fatima',  'last' => 'Ben Ali',     'email' => 'fatima@example.com',  'display' => 'fatima'],
            ['first' => 'Ethan',   'last' => 'Whitaker',    'email' => 'ethan@example.com',   'display' => 'ethan.w'],
            ['first' => 'Aiko',    'last' => 'Kimura',      'email' => 'aiko@example.com',    'display' => 'aiko'],
            ['first' => 'Diego',   'last' => 'Morales',     'email' => 'diego@example.com',   'display' => 'diego.m'],
            ['first' => 'Elena',   'last' => 'Petrova',     'email' => 'elena@example.com',   'display' => 'elena.p'],
            ['first' => 'Kwame',   'last' => 'Mensah',      'email' => 'kwame@example.com',   'display' => 'kwame'],
            ['first' => 'Ingrid',  'last' => 'Solberg',     'email' => 'ingrid@example.com',  'display' => 'ingrid.s'],
            ['first' => 'Nikolai', 'last' => 'Ivanov',      'email' => 'nikolai@example.com', 'display' => 'nikolai'],
            ['first' => 'Rosa',    'last' => 'Almeida',     'email' => 'rosa@example.com',    'display' => 'rosa.a'],
            ['first' => 'Arjun',   'last' => 'Mehta',       'email' => 'arjun@example.com',   'display' => 'arjun.m'],
            ['first' => 'Chloe',   'last' => 'Dubois',      'email' => 'chloe@example.com',   'display' => 'chloe'],
            ['first' => 'Samuel',  'last' => 'Andersen',    'email' => 'samuel@example.com',  'display' => 'samuel.a'],
            ['first' => 'Amina',   'last' => 'Diallo',      'email' => 'amina@example.com',   'display' => 'amina'],
            ['first' => 'Liam',    'last' => "O'Brien",     'email' => 'liam@example.com',    'display' => 'liam.ob'],
            ['first' => 'Sara',    'last' => 'Nowak',       'email' => 'sara@example.com',    'display' => 'sara.n'],
            ['first' => 'Tariq',   'last' => 'Al-Masri',    'email' => 'tariq@example.com',   'display' => 'tariq'],
            ['first' => 'Mia',     'last' => 'Johansson',   'email' => 'mia@example.com',     'display' => 'mia.j'],
            ['first' => 'Ibrahim', 'last' => 'Kone',        'email' => 'ibrahim@example.com', 'display' => 'ibrahim'],
            ['first' => 'Astrid',  'last' => 'Nilsen',      'email' => 'astrid@example.com',  'display' => 'astrid.n'],
            ['first' => 'Hugo',    'last' => 'Costa',       'email' => 'hugo@example.com',    'display' => 'hugo'],
            ['first' => 'Zainab',  'last' => 'Hussein',     'email' => 'zainab@example.com',  'display' => 'zainab.h'],
            ['first' => 'Anders',  'last' => 'Berg',        'email' => 'anders@example.com',  'display' => 'anders'],
            ['first' => 'Lucia',   'last' => 'Marino',      'email' => 'lucia@example.com',   'display' => 'lucia.m'],
            ['first' => 'Youssef', 'last' => 'Bouazizi',    'email' => 'youssef@example.com', 'display' => 'youssef'],
            ['first' => 'Freya',   'last' => 'Halvorsen',   'email' => 'freya@example.com',   'display' => 'freya.h'],
            ['first' => 'Kenji',   'last' => 'Nakamura',    'email' => 'kenji@example.com',   'display' => 'kenji'],
            ['first' => 'Beatriz', 'last' => 'Silva',       'email' => 'beatriz@example.com', 'display' => 'bea.silva'],
            ['first' => 'Anton',   'last' => 'Novak',       'email' => 'anton@example.com',   'display' => 'anton.n'],
            ['first' => 'Nadia',   'last' => 'Farouk',      'email' => 'nadia@example.com',   'display' => 'nadia'],
            ['first' => 'Pedro',   'last' => 'Gonzalez',    'email' => 'pedro@example.com',   'display' => 'pedro.g'],
            ['first' => 'Aisha',   'last' => 'Abdi',        'email' => 'aisha@example.com',   'display' => 'aisha'],
        ];
    }
}
