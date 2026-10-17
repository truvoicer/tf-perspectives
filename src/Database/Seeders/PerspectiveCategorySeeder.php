<?php
// packages/truvoicer/tf-perspectives/database/seeders/PerspectiveCategorySeeder.php

namespace Truvoicer\TfPerspectives\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Truvoicer\TfPerspectives\Models\PerspectiveCategory;

class PerspectiveCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $row) {
            PerspectiveCategory::firstOrCreate(
                ['slug' => Str::slug($row['name'])],
                $row,
            );
        }
    }

    private function rows(): array
    {
        return [
            // ─── Work & Economy ───────────────────────────────────────────
            ['name' => 'Work & Money',          'icon' => 'briefcase',       'color' => '#2f5d50', 'sort_order' => 10],
            ['name' => 'Employment',            'icon' => 'briefcase',       'color' => '#2f5d50', 'sort_order' => 20],
            ['name' => 'Entrepreneurship',      'icon' => 'lightbulb',       'color' => '#2f5d50', 'sort_order' => 30],
            ['name' => 'Freelancing',           'icon' => 'briefcase',       'color' => '#2f5d50', 'sort_order' => 40],
            ['name' => 'Remote Work',           'icon' => 'globe',           'color' => '#2f5d50', 'sort_order' => 50],
            ['name' => 'Gig Economy',           'icon' => 'briefcase',       'color' => '#2f5d50', 'sort_order' => 60],
            ['name' => 'Leadership',            'icon' => 'users',           'color' => '#2f5d50', 'sort_order' => 70],
            ['name' => 'Career Change',         'icon' => 'arrow-trend-up',  'color' => '#2f5d50', 'sort_order' => 80],
            ['name' => 'Poverty & Hardship',    'icon' => 'hand-holding-heart', 'color' => '#2f5d50', 'sort_order' => 90],
            ['name' => 'Wealth & Privilege',    'icon' => 'coins',           'color' => '#2f5d50', 'sort_order' => 100],

            // ─── Family & Relationships ───────────────────────────────────
            ['name' => 'Family',                'icon' => 'house',           'color' => '#b8722f', 'sort_order' => 110],
            ['name' => 'Parenting',             'icon' => 'baby',            'color' => '#b8722f', 'sort_order' => 120],
            ['name' => 'Single Parenting',      'icon' => 'user',            'color' => '#b8722f', 'sort_order' => 130],
            ['name' => 'Marriage & Partnership','icon' => 'heart',           'color' => '#b8722f', 'sort_order' => 140],
            ['name' => 'Divorce & Separation',  'icon' => 'heart-crack',     'color' => '#b8722f', 'sort_order' => 150],
            ['name' => 'Dating & Romance',      'icon' => 'heart',           'color' => '#b8722f', 'sort_order' => 160],
            ['name' => 'Friendship',            'icon' => 'user-group',      'color' => '#b8722f', 'sort_order' => 170],
            ['name' => 'Caregiving',            'icon' => 'hand-holding-heart', 'color' => '#b8722f', 'sort_order' => 180],
            ['name' => 'Elder Care',            'icon' => 'person-cane',     'color' => '#b8722f', 'sort_order' => 190],
            ['name' => 'Childhood',             'icon' => 'child',           'color' => '#b8722f', 'sort_order' => 200],
            ['name' => 'Adolescence',           'icon' => 'child-reaching',  'color' => '#b8722f', 'sort_order' => 210],
            ['name' => 'Grief & Loss',          'icon' => 'dove',            'color' => '#b8722f', 'sort_order' => 220],

            // ─── Health & Body ────────────────────────────────────────────
            ['name' => 'Health',                'icon' => 'heart',           'color' => '#a33b2a', 'sort_order' => 230],
            ['name' => 'Mental Health',         'icon' => 'brain',           'color' => '#a33b2a', 'sort_order' => 240],
            ['name' => 'Chronic Illness',       'icon' => 'heart-pulse',     'color' => '#a33b2a', 'sort_order' => 250],
            ['name' => 'Disability',            'icon' => 'wheelchair',      'color' => '#a33b2a', 'sort_order' => 260],
            ['name' => 'Neurodiversity',        'icon' => 'brain',           'color' => '#a33b2a', 'sort_order' => 270],
            ['name' => 'Aging',                 'icon' => 'person-cane',     'color' => '#a33b2a', 'sort_order' => 280],
            ['name' => 'Addiction & Recovery',  'icon' => 'heart-crack',     'color' => '#a33b2a', 'sort_order' => 290],
            ['name' => 'Body Image',            'icon' => 'person',          'color' => '#a33b2a', 'sort_order' => 300],
            ['name' => 'Fitness & Sport',       'icon' => 'person-running',  'color' => '#a33b2a', 'sort_order' => 310],
            ['name' => 'Nutrition',             'icon' => 'apple-whole',     'color' => '#a33b2a', 'sort_order' => 320],
            ['name' => 'Sleep',                 'icon' => 'moon',            'color' => '#a33b2a', 'sort_order' => 330],
            ['name' => 'Palliative Care',       'icon' => 'hand-holding-heart', 'color' => '#a33b2a', 'sort_order' => 340],

            // ─── Belonging & Identity ─────────────────────────────────────
            ['name' => 'Belonging',             'icon' => 'users',           'color' => '#4c6ef5', 'sort_order' => 350],
            ['name' => 'Race & Ethnicity',      'icon' => 'earth-americas',  'color' => '#4c6ef5', 'sort_order' => 360],
            ['name' => 'Gender',                'icon' => 'venus-mars',      'color' => '#4c6ef5', 'sort_order' => 370],
            ['name' => 'LGBTQ+',                'icon' => 'rainbow',         'color' => '#4c6ef5', 'sort_order' => 380],
            ['name' => 'Immigration',           'icon' => 'plane',           'color' => '#4c6ef5', 'sort_order' => 390],
            ['name' => 'Refugees',              'icon' => 'person-through-window', 'color' => '#4c6ef5', 'sort_order' => 400],
            ['name' => 'Language & Culture',    'icon' => 'language',        'color' => '#4c6ef5', 'sort_order' => 410],
            ['name' => 'Religion',              'icon' => 'place-of-worship','color' => '#4c6ef5', 'sort_order' => 420],
            ['name' => 'Spirituality',          'icon' => 'om',              'color' => '#4c6ef5', 'sort_order' => 430],
            ['name' => 'Community',             'icon' => 'people-group',    'color' => '#4c6ef5', 'sort_order' => 440],
            ['name' => 'Friendship & Social Life', 'icon' => 'user-group',   'color' => '#4c6ef5', 'sort_order' => 450],
            ['name' => 'Loneliness',            'icon' => 'user',            'color' => '#4c6ef5', 'sort_order' => 460],

            // ─── Learning & Knowledge ─────────────────────────────────────
            ['name' => 'Learning',              'icon' => 'graduation-cap',  'color' => '#0c8599', 'sort_order' => 470],
            ['name' => 'Education',             'icon' => 'school',          'color' => '#0c8599', 'sort_order' => 480],
            ['name' => 'Higher Education',      'icon' => 'university',      'color' => '#0c8599', 'sort_order' => 490],
            ['name' => 'Student Life',          'icon' => 'book-open',       'color' => '#0c8599', 'sort_order' => 500],
            ['name' => 'Teaching',              'icon' => 'chalkboard-user', 'color' => '#0c8599', 'sort_order' => 510],
            ['name' => 'Research',              'icon' => 'microscope',      'color' => '#0c8599', 'sort_order' => 520],
            ['name' => 'Science',               'icon' => 'flask',           'color' => '#0c8599', 'sort_order' => 530],
            ['name' => 'Philosophy',            'icon' => 'brain',           'color' => '#0c8599', 'sort_order' => 540],
            ['name' => 'History',               'icon' => 'landmark',        'color' => '#0c8599', 'sort_order' => 550],
            ['name' => 'Books & Literature',    'icon' => 'book',            'color' => '#0c8599', 'sort_order' => 560],

            // ─── Environment & Place ──────────────────────────────────────
            ['name' => 'Environment',           'icon' => 'leaf',            'color' => '#2b8a3e', 'sort_order' => 570],
            ['name' => 'Climate Change',        'icon' => 'temperature-high','color' => '#2b8a3e', 'sort_order' => 580],
            ['name' => 'Sustainability',        'icon' => 'recycle',         'color' => '#2b8a3e', 'sort_order' => 590],
            ['name' => 'Conservation',          'icon' => 'tree',            'color' => '#2b8a3e', 'sort_order' => 600],
            ['name' => 'Animals & Wildlife',    'icon' => 'paw',             'color' => '#2b8a3e', 'sort_order' => 610],
            ['name' => 'Rural Life',            'icon' => 'tractor',         'color' => '#2b8a3e', 'sort_order' => 620],
            ['name' => 'Urban Life',            'icon' => 'city',            'color' => '#2b8a3e', 'sort_order' => 630],
            ['name' => 'Housing',               'icon' => 'house',           'color' => '#2b8a3e', 'sort_order' => 640],
            ['name' => 'Homelessness',          'icon' => 'house-chimney-crack', 'color' => '#2b8a3e', 'sort_order' => 650],
            ['name' => 'Transport & Mobility',  'icon' => 'bus',             'color' => '#2b8a3e', 'sort_order' => 660],

            // ─── Justice & Power ──────────────────────────────────────────
            ['name' => 'Justice',               'icon' => 'scale-balanced',  'color' => '#7048e8', 'sort_order' => 670],
            ['name' => 'Law & Legal',           'icon' => 'gavel',           'color' => '#7048e8', 'sort_order' => 680],
            ['name' => 'Politics',              'icon' => 'landmark',        'color' => '#7048e8', 'sort_order' => 690],
            ['name' => 'Policy',                'icon' => 'file-lines',      'color' => '#7048e8', 'sort_order' => 700],
            ['name' => 'Activism',              'icon' => 'bullhorn',        'color' => '#7048e8', 'sort_order' => 710],
            ['name' => 'Human Rights',          'icon' => 'hand-fist',       'color' => '#7048e8', 'sort_order' => 720],
            ['name' => 'Inequality',            'icon' => 'scale-unbalanced','color' => '#7048e8', 'sort_order' => 730],
            ['name' => 'Corruption',            'icon' => 'hand',            'color' => '#7048e8', 'sort_order' => 740],
            ['name' => 'Democracy',             'icon' => 'person-booth',    'color' => '#7048e8', 'sort_order' => 750],
            ['name' => 'Protest & Dissent',     'icon' => 'bullhorn',        'color' => '#7048e8', 'sort_order' => 760],

            // ─── Safety & Crisis ──────────────────────────────────────────
            ['name' => 'Safety',                'icon' => 'shield-halved',   'color' => '#495057', 'sort_order' => 770],
            ['name' => 'Crime',                 'icon' => 'mask',            'color' => '#495057', 'sort_order' => 780],
            ['name' => 'Violence',              'icon' => 'shield-halved',   'color' => '#495057', 'sort_order' => 790],
            ['name' => 'Abuse & Trauma',        'icon' => 'heart-crack',     'color' => '#495057', 'sort_order' => 800],
            ['name' => 'War & Conflict',        'icon' => 'jet-fighter',     'color' => '#495057', 'sort_order' => 810],
            ['name' => 'Displacement',          'icon' => 'person-walking-arrow-right', 'color' => '#495057', 'sort_order' => 820],
            ['name' => 'Emergency Services',    'icon' => 'truck-medical',   'color' => '#495057', 'sort_order' => 830],
            ['name' => 'Disaster & Recovery',   'icon' => 'house-flood-water','color' => '#495057', 'sort_order' => 840],
            ['name' => 'Policing',              'icon' => 'shield-halved',   'color' => '#495057', 'sort_order' => 850],

            // ─── Faith & Meaning ──────────────────────────────────────────
            ['name' => 'Faith & Meaning',       'icon' => 'globe',           'color' => '#c2255c', 'sort_order' => 860],
            ['name' => 'Christianity',          'icon' => 'cross',           'color' => '#c2255c', 'sort_order' => 870],
            ['name' => 'Islam',                 'icon' => 'star-and-crescent','color' => '#c2255c', 'sort_order' => 880],
            ['name' => 'Judaism',               'icon' => 'star-of-david',   'color' => '#c2255c', 'sort_order' => 890],
            ['name' => 'Hinduism',              'icon' => 'om',              'color' => '#c2255c', 'sort_order' => 900],
            ['name' => 'Buddhism',              'icon' => 'dharmachakra',    'color' => '#c2255c', 'sort_order' => 910],
            ['name' => 'Indigenous Traditions', 'icon' => 'feather',         'color' => '#c2255c', 'sort_order' => 920],
            ['name' => 'Atheism & Secularism',  'icon' => 'circle-half-stroke','color' => '#c2255c', 'sort_order' => 930],
            ['name' => 'Meaning & Purpose',     'icon' => 'compass',         'color' => '#c2255c', 'sort_order' => 940],

            // ─── Technology & Future ──────────────────────────────────────
            ['name' => 'Technology',            'icon' => 'brain',           'color' => '#1098ad', 'sort_order' => 950],
            ['name' => 'Artificial Intelligence','icon' => 'robot',          'color' => '#1098ad', 'sort_order' => 960],
            ['name' => 'Privacy & Surveillance','icon' => 'eye',             'color' => '#1098ad', 'sort_order' => 970],
            ['name' => 'Social Media',          'icon' => 'share-nodes',     'color' => '#1098ad', 'sort_order' => 980],
            ['name' => 'Cybersecurity',         'icon' => 'shield-halved',   'color' => '#1098ad', 'sort_order' => 990],
            ['name' => 'Open Source',           'icon' => 'code-branch',     'color' => '#1098ad', 'sort_order' => 1000],
            ['name' => 'Startups',              'icon' => 'rocket',          'color' => '#1098ad', 'sort_order' => 1010],
            ['name' => 'Digital Rights',        'icon' => 'fingerprint',     'color' => '#1098ad', 'sort_order' => 1020],
            ['name' => 'Automation & Jobs',     'icon' => 'robot',           'color' => '#1098ad', 'sort_order' => 1030],
            ['name' => 'Space & Exploration',   'icon' => 'rocket',          'color' => '#1098ad', 'sort_order' => 1040],

            // ─── Arts & Expression ────────────────────────────────────────
            ['name' => 'Arts & Culture',        'icon' => 'palette',         'color' => '#ae3ec9', 'sort_order' => 1050],
            ['name' => 'Music',                 'icon' => 'music',           'color' => '#ae3ec9', 'sort_order' => 1060],
            ['name' => 'Film & Television',     'icon' => 'film',            'color' => '#ae3ec9', 'sort_order' => 1070],
            ['name' => 'Writing',               'icon' => 'pen-nib',         'color' => '#ae3ec9', 'sort_order' => 1080],
            ['name' => 'Visual Art',            'icon' => 'palette',         'color' => '#ae3ec9', 'sort_order' => 1090],
            ['name' => 'Theatre & Performance', 'icon' => 'masks-theater',   'color' => '#ae3ec9', 'sort_order' => 1100],
            ['name' => 'Photography',           'icon' => 'camera',          'color' => '#ae3ec9', 'sort_order' => 1110],
            ['name' => 'Design',                'icon' => 'pen-ruler',       'color' => '#ae3ec9', 'sort_order' => 1120],
            ['name' => 'Crafts & Making',       'icon' => 'scissors',        'color' => '#ae3ec9', 'sort_order' => 1130],
            ['name' => 'Food & Cooking',        'icon' => 'utensils',        'color' => '#ae3ec9', 'sort_order' => 1140],
            ['name' => 'Travel',                'icon' => 'plane',           'color' => '#ae3ec9', 'sort_order' => 1150],
            ['name' => 'Humor',                 'icon' => 'face-laugh',      'color' => '#ae3ec9', 'sort_order' => 1160],

            // ─── Everyday Life ────────────────────────────────────────────
            ['name' => 'Everyday Life',         'icon' => 'mug-hot',         'color' => '#f08c00', 'sort_order' => 1170],
            ['name' => 'Time & Busyness',       'icon' => 'clock',           'color' => '#f08c00', 'sort_order' => 1180],
            ['name' => 'Money & Budgeting',     'icon' => 'coins',           'color' => '#f08c00', 'sort_order' => 1190],
            ['name' => 'Consumption & Stuff',   'icon' => 'box',             'color' => '#f08c00', 'sort_order' => 1200],
            ['name' => 'Digital Life',          'icon' => 'mobile-screen',   'color' => '#f08c00', 'sort_order' => 1210],
            ['name' => 'Neighbours & Local',    'icon' => 'house-user',      'color' => '#f08c00', 'sort_order' => 1220],
            ['name' => 'Pets',                  'icon' => 'paw',             'color' => '#f08c00', 'sort_order' => 1230],
            ['name' => 'Celebrations & Rituals','icon' => 'cake-candles',    'color' => '#f08c00', 'sort_order' => 1240],
            ['name' => 'Seasons & Weather',     'icon' => 'cloud-sun',       'color' => '#f08c00', 'sort_order' => 1250],
        ];
    }
}
