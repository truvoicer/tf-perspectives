<?php

namespace Truvoicer\TfPerspectives\Models;

use BackedEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Truvoicer\TfPerspectives\Enums\Block\Block as EnumsBlock;
use Truvoicer\TruFetcherCore\Enums\Block\Block;
use Truvoicer\TruFetcherCore\Models\File;
use Truvoicer\TruFetcherCore\Models\PageColumn;
use Truvoicer\TruFetcherCore\Models\PageColumnBlock as ModelsPageColumnBlock;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock query()
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock wherePageColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock whereBlock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock create(array $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock findOrFail(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|PageColumnBlock first()
 *
 * @property int $id
 * @property int $page_column_id
 * @property string $uid
 * @property string $block
 * @property array|null $data
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read PageColumn $column
 * @property-read Collection<int, File> $files
 */
class PageColumnBlock extends ModelsPageColumnBlock
{

    /**
     * Get the block
     *
     * @return Block
     */
    public function getBlock(): BackedEnum
    {
        $block = Block::tryFrom($this->attributes['block']);
        if (! $block instanceof Block) {
            $block = EnumsBlock::tryFrom($this->attributes['block']);
        }
        if (! $block instanceof Block && ! $block instanceof EnumsBlock) {
            throw new \InvalidArgumentException(sprintf(
                'Block must be instance of %s or %s, got %s',
                Block::class,
                EnumsBlock::class,
                is_object($block) ? get_class($block) : gettype($block)
            ));
        }
        return $block;
    }

    /**
     * Set the block
     *
     * @throws \InvalidArgumentException
     */
    public function setBlock(BackedEnum $block): self
    {
        if (! $block instanceof Block && ! $block instanceof EnumsBlock) {
            throw new \InvalidArgumentException(sprintf(
                'Block must be instance of %s or %s, got %s',
                Block::class,
                EnumsBlock::class,
                is_object($block) ? get_class($block) : gettype($block)
            ));
        }
        $this->attributes['block'] = $block->value;

        return $this;
    }

}
