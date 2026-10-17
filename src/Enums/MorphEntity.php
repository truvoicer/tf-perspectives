<?php
// packages/truvoicer/tf-perspectives/src/Enums/MorphEntity.php

namespace Truvoicer\TfPerspectives\Enums;

use Truvoicer\TfPerspectives\Models\User;

enum MorphEntity: string
{
    case USER = User::class;

    public function morphType(): string
    {
        return match ($this) {
            self::USER => 'user',
        };
    }
}
