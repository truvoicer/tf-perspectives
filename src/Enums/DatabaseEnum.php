<?php

namespace Truvoicer\TfPerspectives\Enums;

use Truvoicer\TfPerspectives\Enums\Block\Block;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock;

enum DatabaseEnum: string
{

    case BLOCK = 'block';

    public function label(): string
    {
        return match ($this) {
            self::BLOCK => 'Block',
        };
    }

    public function enum(): string
    {
        return match ($this) {
            self::BLOCK => Block::class,
        };
    }

    public function model(): array
    {
        return match ($this) {
            self::BLOCK => [
                PageColumnBlock::class => ['block'],
            ],
        };
    }
}
