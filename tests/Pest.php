<?php

use Truvoicer\TfPerspectives\Models\Comment;
use Truvoicer\TfPerspectives\Models\User;
use Truvoicer\TfPerspectives\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeComment', function () {
    return $this->toBeInstanceOf(Comment::class);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function createUser($attributes = [])
{
    return User::factory()->create($attributes);
}

function createComment($attributes = [])
{
    return Comment::factory()->create($attributes);
}
